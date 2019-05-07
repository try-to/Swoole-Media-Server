<?php

namespace TrytoMediaServer\Protocols;

use TrytoMediaServer\Protocols\ProtocolInterface;

/**
 * rtmp protocol
 */
class Rtmp implements ProtocolInterface
{

    /**
     * Check the integrity of the package.
     *
     * @param string  $buffer
     * @return int
     */
    public static function input(string $buffer)
    {
        return $buffer;
    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public static function encode(string $payload)
    {
        return $payload;
    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public static function decode(string $buffer)
    {
        return $buffer;
    }
}
