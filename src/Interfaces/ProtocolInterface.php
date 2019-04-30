<?php declare(strict_types=1);

namespace TrytoMediaServer\Interfaces;

interface ProtocolInterface
{
    /**
     * Check the integrity of the package.
     * @param string              $buffer
     * @return int
     */
    public function input(string $buffer): int;
    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    public function encode(string $payload): string;
    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    public function decode(string $buffer): string;
}
