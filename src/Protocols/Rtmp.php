<?php

namespace TrytoMediaServer\Protocols;

use TrytoMediaServer\Protocols\ProtocolInterface;
use TrytoMediaServer\Protocols\Rtmp\RtmpOperation;
use TrytoMediaServer\Protocols\Rtmp\RtmpPacket;
use TrytoMediaServer\Protocols\Rtmp\RtmpStream;

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

    /**
     * Check the integrity of the package.
     *
     * @param string  $buffer
     * @return int
     */
    public static function input($buffer)
    {
        return $buffer;
    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public static function encode($payload)
    {
        return $payload;
    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        // $stream = new RtmpStream();
        $c0 = self::readBuffer($buffer, 0, 1)->readTinyInt();

        $c1 = self::readBuffer($buffer, 1, 1536);

        // $c1 = self::readBuffer($buffer, 1536);
        // $packet = self::readPacket($buffer);

        echo 'C0'.PHP_EOL;
        var_dump($c0);
        echo PHP_EOL.'C1'.PHP_EOL;
        var_dump($c1);

        return $buffer;
    }

    // private static function readPacket($buffer)
    // {
    //     $packet = new RtmpPacket();
    //     $header = self::readBuffer($buffer, 0)->readTinyInt();

    //     $packet->chunkType = (($header & 0xc0) >> 6);
    //     $packet->chunkStreamId = $header & 0x3f;

    //     switch ($packet->chunkStreamId) {
    //         case 0: //range of 64-319, second byte + 64
    //             $packet->chunkStreamId = 64 + self::readBuffer($buffer, 1)->readTinyInt();
    //             break;
    //         case 1: //range of 64-65599,thrid byte * 256 + second byte + 64
    //             $packet->chunkStreamId = 64 + self::readBuffer($buffer, 1)->readTinyInt() + self::readBuffer($buffer, 1)->readTinyInt() * 256;
    //             break;
    //         case 2:
    //             break;
    //         default: //range of 3-63
    //             // complete stream ids
    //     }

    //     switch ($packet->chunkType) {
    //         case RtmpPacket::CHUNK_TYPE_3:
    //             $packet->timestamp = self::$prevReadingPacket[$packet->chunkStreamId]->timestamp;
    //         // no break
    //         case RtmpPacket::CHUNK_TYPE_2:
    //             $packet->length = self::$prevReadingPacket[$packet->chunkStreamId]->length;
    //             $packet->type = self::$prevReadingPacket[$packet->chunkStreamId]->type;
    //         // no break
    //         case RtmpPacket::CHUNK_TYPE_1:
    //             $packet->streamId = self::$prevReadingPacket[$packet->chunkStreamId]->streamId;
    //         // no break
    //         case RtmpPacket::CHUNK_TYPE_0:
    //             break;
    //     }

    //     self::$prevReadingPacket[$packet->chunkStreamId] = $packet;
    //     $headerSize = RtmpPacket::$SIZES[$packet->chunkType];

    //     if ($headerSize == RtmpPacket::MAX_HEADER_SIZE) {
    //         $packet->hasAbsTimestamp = true;
    //     }

    //     if (!isset(self::$operations[$packet->chunkStreamId])) {
    //         self::$operations[$packet->chunkStreamId] = new RtmpOperation();
    //     }

    //     if (self::$operations[$packet->chunkStreamId]->getResponse()) {
    //         //Operation chunking....
    //         $packet = self::$operations[$packet->chunkStreamId]->getResponse()->getPacket();
    //         $headerSize = 0; //no header
    //     } else {
    //         //Create response from packet
    //         self::$operations[$packet->chunkStreamId]->createResponse($packet);
    //     }

    //     $headerSize--;
    //     $header;

    //     if ($headerSize > 0) {
    //         $header = self::readBuffer($buffer, $headerSize);
    //     }

    //     if ($headerSize >= 3) {
    //         $packet->timestamp = $header->readInt24();
    //     }

    //     if ($headerSize >= 6) {
    //         $packet->length = $header->readInt24();

    //         $packet->bytesRead = 0;
    //         $packet->free();
    //     }
    //     if ($headerSize > 6) {
    //         $packet->type = $header->readTinyInt();
    //     }

    //     if ($headerSize == 11) {
    //         $packet->streamId = $header->readInt32LE();
    //     }

    //     $nToRead = $packet->length - $packet->bytesRead;
    //     $nChunk = self::$chunkSizeR;
    //     if ($nToRead < $nChunk) {
    //         $nChunk = $nToRead;
    //     }

    //     if ($packet->isReady()) {
    //         return $packet;
    //     }

    //     return null;
    // }

    private static function readBuffer($buffer, $index, $length)
    {
        return new RtmpStream(substr($buffer, $index, $length));
    }
}
