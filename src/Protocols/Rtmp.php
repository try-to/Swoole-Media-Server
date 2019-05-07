<?php

namespace TrytoMediaServer\Protocols;

use TrytoMediaServer\Interfaces\ProtocolInterface;

/**
 * rtmp protocol
 */
class Rtmp extends ProtocolInterface
{

    /**
     * Check the integrity of the package.
     *
     * @param string  $buffer
     * @return int
     */
    protected static function input(string $buffer)
    {
        return $buffer;
    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    protected static function encode(string $payload)
    {
        return $payload;
    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    protected static function decode(string $buffer)
    {
        return $buffer;
    }
}
