<?php

namespace TrytoMediaServer\Protocols;

use TrytoMediaServer\Protocols\ProtocolInterface;
use TrytoMediaServer\Protocols\Rtmp\RtmpOperation;
use TrytoMediaServer\Protocols\Rtmp\RtmpPacket;
use TrytoMediaServer\Protocols\Rtmp\RtmpStream;

/**
 * rtmp protocol
 */
class Rtmp
{

    public $prevReadingPacket = array();

    public $chunkSizeR = 128, $chunkSizeW = 128;

    public $operations = array();

    public $handshakeState = 0;

    public $fd = null;

    public $server = null;

    public $c0 = 0;
    public $c1 = 0;
    public $c2 = 0;

    public function __construct(int $fd,\swoole_server $server)
    {
        $this->fd = $fd;
        $this->server = $server;
        $this->handshakeState = 0;
        $this->c0 = 0;
        $this->c1 = 0;
        $this->c2 = 0;
    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public function decode($buffer)
    {
        // RTMP 握手验证
        switch ($this->handshakeState) {
            case RtmpPacket::RTMP_HANDSHAKE_0:
                if ((strlen($buffer) == (RtmpPacket::RTMP_SIG_SIZE + 1)) && !$this->c0 && !$this->c1) {
                    $this->c0 = $this->readBuffer($buffer, 0, 1)->readTinyInt();
                    $this->c1 = $this->readBuffer($buffer, 1, RtmpPacket::RTMP_SIG_SIZE);
                } elseif (strlen($buffer) == 1 && !$this->c0) {
                    $this->c0 = $this->readBuffer($buffer, 0, 1)->readTinyInt();
                } elseif (strlen($buffer) == RtmpPacket::RTMP_SIG_SIZE && !$this->c1) {
                    $this->c1 = $this->readBuffer($buffer, 0, RtmpPacket::RTMP_SIG_SIZE);
                }
                if ($this->c0 && $this->c1) {
                    // 收到c0 c1 发送s0 s1 s2
                    $stream = new RtmpStream();
                    $stream->writeByte(3); // 当前RTMP协议的版本为 3
                    $this->server->send($this->fd, $stream->dump());

                    // s1
                    $stream = new RtmpStream();
                    $ctime = time();
                    $stream->writeInt32($ctime); //Time 4
                    $stream->writeInt32(0); // zero 4
                    for ($i = 0; $i < RtmpPacket::RTMP_SIG_SIZE - 8; $i++) {
                        $stream->writeByte(rand(0, 256));
                    }
                    $this->server->send($this->fd, $stream->dump());

                    // s2
                    $stream = new RtmpStream();
                    $stream->writeInt32($this->c1->readInt32());
                    $this->c1->readInt32();
                    $stream->writeInt32($ctime);
                    $raw = $this->c1->readRaw();
                    $stream->write($raw);
                    $this->server->send($this->fd, $stream->dump());

                    $this->handshakeState = RtmpPacket::RTMP_HANDSHAKE_1;
                }
                break;
            case RtmpPacket::RTMP_HANDSHAKE_1:
                // 收到c2
                $this->c2 = $this->readBuffer($buffer, 0, RtmpPacket::RTMP_SIG_SIZE)->readRaw();
                if (!empty($this->c2)) {
                    $this->handshakeState = RtmpPacket::RTMP_HANDSHAKE_2;
                }
                $this->c0 = 0;
                $this->c1 = 0;
                break;
            case RtmpPacket::RTMP_HANDSHAKE_2:
                $this->rtmpChunkRead($buffer);
                break;
            default:
                break;
        }
        return false;
    }


    private function rtmpChunkRead($buffer)
    {
        echo 'packet:' . PHP_EOL;
        var_dump($buffer);
    }

    private function readPacket($buffer)
    {
        $packet = new RtmpPacket();
        $header = $this->readBuffer($buffer, 0, 1)->readTinyInt();

        $packet->chunkType = (($header & 0xc0) >> 6);
        $packet->chunkStreamId = $header & 0x3f;

        switch ($packet->chunkStreamId) {
            case 0: //range of 64-319, second byte + 64
                $packet->chunkStreamId = 64 + $this->readBuffer($buffer, 0, 1)->readTinyInt();
                break;
            case 1: //range of 64-65599,thrid byte * 256 + second byte + 64
                $packet->chunkStreamId = 64 + $this->readBuffer($buffer, 0, 1)->readTinyInt() + $this->readBuffer($buffer, 0, 1)->readTinyInt() * 256;
                break;
            case 2:
                break;
            default: //range of 3-63
                // complete stream ids
        }

        switch ($packet->chunkType) {
            case RtmpPacket::CHUNK_TYPE_3:
                $packet->timestamp = $this->prevReadingPacket[$packet->chunkStreamId]->timestamp;
            // no break
            case RtmpPacket::CHUNK_TYPE_2:
                $packet->length = $this->prevReadingPacket[$packet->chunkStreamId]->length;
                $packet->type = $this->prevReadingPacket[$packet->chunkStreamId]->type;
            // no break
            case RtmpPacket::CHUNK_TYPE_1:
                $packet->streamId = $this->prevReadingPacket[$packet->chunkStreamId]->streamId;
            // no break
            case RtmpPacket::CHUNK_TYPE_0:
                break;
        }

        $this->prevReadingPacket[$packet->chunkStreamId] = $packet;
        $headerSize = RtmpPacket::$SIZES[$packet->chunkType];

        if ($headerSize == RtmpPacket::MAX_HEADER_SIZE) {
            $packet->hasAbsTimestamp = true;
        }

        if (!isset($this->operations[$packet->chunkStreamId])) {
            $this->operations[$packet->chunkStreamId] = new RtmpOperation();
        }

        if ($this->operations[$packet->chunkStreamId]->getResponse()) {
            //Operation chunking....
            $packet = $this->operations[$packet->chunkStreamId]->getResponse()->getPacket();
            $headerSize = 0; //no header
        } else {
            //Create response from packet
            $this->operations[$packet->chunkStreamId]->createResponse($packet);
        }

        $headerSize--;
        $header;

        if ($headerSize > 0) {
            $header = $this->readBuffer($buffer, 0, $headerSize);
        }

        if ($headerSize >= 3) {
            $packet->timestamp = $header->readInt24();
        }

        if ($headerSize >= 6) {
            $packet->length = $header->readInt24();

            $packet->bytesRead = 0;
            $packet->free();
        }
        if ($headerSize > 6) {
            $packet->type = $header->readTinyInt();
        }

        if ($headerSize == 11) {
            $packet->streamId = $header->readInt32LE();
        }

        $nToRead = $packet->length - $packet->bytesRead;
        $nChunk = $this->chunkSizeR;
        if ($nToRead < $nChunk) {
            $nChunk = $nToRead;
        }

        if ($packet->isReady()) {
            return $packet;
        }

        return null;
    }

    private function readBuffer($buffer, $index = 0, $length = 1)
    {
        return new RtmpStream(substr($buffer, $index, $length));
    }
}
