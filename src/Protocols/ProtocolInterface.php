<?php declare (strict_types = 1);

namespace TrytoMediaServer\Protocols;

/**
 * Protocol interface
 */
interface ProtocolInterface
{
    /**
     * Check the integrity of the package.
     * @param string              $buffer
     * @return int
     */
    public static function input(string $buffer, int $fd, \swoole_server $server);

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public static function encode(string $payload, int $fd, \swoole_server $server);

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public static function decode(string $buffer, int $fd, \swoole_server $server);
}
