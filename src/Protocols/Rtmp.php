<?php

namespace TrytoMediaServer\Protocols;

use TrytoMediaServer\Protocols\ProtocolInterface;
use TrytoMediaServer\Protocols\Rtmp\RtmpPacket;
use TrytoMediaServer\Protocols\Rtmp\RtmpStream;
use TrytoMediaServer\Protocols\Rtmp\RtmpOperation;

/**
 * rtmp protocol
 */
class Rtmp implements ProtocolInterface
{

    /**
     * Previous packet
     * @internal
     *
     * @var RtmpPacket
     */
    private static $prevReadingPacket = array();

    private static $chunkSizeR = 128, $chunkSizeW = 128;

    private static $operations = array();

    private static $handshake = 0;
    private static $c0 = 0;
    private static $c1 = 0;
    private static $c2 = 0;

    /**
     * Check the integrity of the package.
     *
     * @param string  $buffer
     * @return int
     */
    public static function input($buffer, $fd, $server)
    {
        return $buffer;
    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public static function encode($payload, $fd, $server)
    {
        return $payload;
    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public static function decode($buffer, $fd, $server)
    {
        // $stream = new RtmpStream();
        if (self::$handshake == 0) {
            if (strlen($buffer) == (RtmpPacket::RTMP_SIG_SIZE + 1)) {
                self::$c0 = self::readBuffer($buffer, 0, 1)->readTinyInt();
                self::$c1 = self::readBuffer($buffer, 1, RtmpPacket::RTMP_SIG_SIZE)->readRaw();
            } else if (strlen($buffer) == 1) {
                self::$c0 = self::readBuffer($buffer, 0, 1)->readTinyInt();
            } else if (strlen($buffer) == RtmpPacket::RTMP_SIG_SIZE) {
                self::$c1 = self::readBuffer($buffer, 0, RtmpPacket::RTMP_SIG_SIZE)->readRaw();
            }
            echo 'handshake:' . self::$handshake . PHP_EOL;
        }

        if (self::$c0 && self::$c1 && self::$handshake == 0) {
            // 收到c0 c1 发送s0 s1 s2
            $stream = new RtmpStream();
            $stream->writeByte(3); // 当前RTMP协议的版本为 3
            $server->send($fd, $stream->dump());

            $stream = new RtmpStream();
            $ctime = time();
            $stream->writeInt32($ctime); //Time 4
            $stream->writeInt32(0); // zero 4
            for ($i = 0; $i < RtmpPacket::RTMP_SIG_SIZE - 8; $i++) {
                $stream->writeByte(rand(0, 256));
            }
            $server->send($fd, $stream->dump());

            $stream = new RtmpStream();
            $stream->write(self::$c1);
            $server->send($fd, $stream->dump());

            self::$handshake = 1;
            echo 'handshake:' . self::$handshake . PHP_EOL;
        }

        if (self::$handshake == 1) {
            // 收到c2
            echo 'handshake:' . self::$handshake . PHP_EOL;
            self::$c2 = self::readBuffer($buffer, 0, RtmpPacket::RTMP_SIG_SIZE)->dump();
            if(!empty(self::$c2)){
                // 发送S2
                // $server->send($fd, self::$c1);
                self::$handshake = 2;
            }
        }

        if (self::$handshake == 2) {
            echo PHP_EOL.'packet:'. PHP_EOL;
            var_dump($buffer);
        }

        return $buffer;
    }

    private static function readPacket($buffer)
    {
        $packet = new RtmpPacket();
        $header = self::readBuffer($buffer, 0, 1)->readTinyInt();

        $packet->chunkType = (($header & 0xc0) >> 6);
        $packet->chunkStreamId = $header & 0x3f;

        switch ($packet->chunkStreamId) {
            case 0: //range of 64-319, second byte + 64
                $packet->chunkStreamId = 64 + self::readBuffer($buffer, 0, 1)->readTinyInt();
                break;
            case 1: //range of 64-65599,thrid byte * 256 + second byte + 64
                $packet->chunkStreamId = 64 + self::readBuffer($buffer, 0, 1)->readTinyInt() + self::readBuffer($buffer, 0, 1)->readTinyInt() * 256;
                break;
            case 2:
                break;
            default: //range of 3-63
                // complete stream ids
        }

        switch ($packet->chunkType) {
            case RtmpPacket::CHUNK_TYPE_3:
                $packet->timestamp = self::$prevReadingPacket[$packet->chunkStreamId]->timestamp;
            // no break
            case RtmpPacket::CHUNK_TYPE_2:
                $packet->length = self::$prevReadingPacket[$packet->chunkStreamId]->length;
                $packet->type = self::$prevReadingPacket[$packet->chunkStreamId]->type;
            // no break
            case RtmpPacket::CHUNK_TYPE_1:
                $packet->streamId = self::$prevReadingPacket[$packet->chunkStreamId]->streamId;
            // no break
            case RtmpPacket::CHUNK_TYPE_0:
                break;
        }

        self::$prevReadingPacket[$packet->chunkStreamId] = $packet;
        $headerSize = RtmpPacket::$SIZES[$packet->chunkType];

        if ($headerSize == RtmpPacket::MAX_HEADER_SIZE) {
            $packet->hasAbsTimestamp = true;
        }

        if (!isset(self::$operations[$packet->chunkStreamId])) {
            self::$operations[$packet->chunkStreamId] = new RtmpOperation();
        }

        if (self::$operations[$packet->chunkStreamId]->getResponse()) {
            //Operation chunking....
            $packet = self::$operations[$packet->chunkStreamId]->getResponse()->getPacket();
            $headerSize = 0; //no header
        } else {
            //Create response from packet
            self::$operations[$packet->chunkStreamId]->createResponse($packet);
        }

        $headerSize--;
        $header;

        if ($headerSize > 0) {
            $header = self::readBuffer($buffer, 0, $headerSize);
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
        $nChunk = self::$chunkSizeR;
        if ($nToRead < $nChunk) {
            $nChunk = $nToRead;
        }

        if ($packet->isReady()) {
            return $packet;
        }

        return null;
    }

    private static function readBuffer($buffer, $index = 0, $length = 1)
    {
        return new RtmpStream(substr($buffer, $index, $length));
    }
}
