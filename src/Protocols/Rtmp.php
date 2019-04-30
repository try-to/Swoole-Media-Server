<?php declare(strict_types=1);

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
    public static function input(string $buffer)
    {

    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public static function encode(string $payload)
    {

    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public static function decode(string $bytes)
    {

    }
}