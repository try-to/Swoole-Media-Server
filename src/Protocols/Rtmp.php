<?php declare(strict_types=1);

namespace TrytoMediaServer\Protocols;

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
    public static function input(string $buffer)
    {

    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public static function encode($payload)
    {

    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public static function decode($bytes)
    {

    }
}