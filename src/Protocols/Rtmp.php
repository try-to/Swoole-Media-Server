<?php declare(strict_types=1);

namespace TrytoMediaServer\Protocols;

use TrytoMediaServer\Interfaces\ProtocolInterface;
use function TrytoMediaServer\Helper\getBytes;

/**
 * rtmp protocol
 */
class Rtmp extends ProtocolInterface
{   

    const N_CHUNK_STREAM = 8;
    const RTMP_VERSION = 3;
    const RTMP_HANDSHAKE_SIZE = 1536;
    const RTMP_HANDSHAKE_UNINIT = 0;
    const RTMP_HANDSHAKE_0 = 1;
    const RTMP_HANDSHAKE_1 = 2;
    const RTMP_HANDSHAKE_2 = 3;

    const RTMP_PARSE_INIT = 0;
    const RTMP_PARSE_BASIC_HEADER = 1;
    const RTMP_PARSE_MESSAGE_HEADER = 2;
    const RTMP_PARSE_EXTENDED_TIMESTAMP = 3;
    const RTMP_PARSE_PAYLOAD = 4;

    const MAX_CHUNK_HEADER = 18;

    const RTMP_CHUNK_TYPE_0 = 0; // 11-bytes: timestamp(3) + length(3) + stream type(1) + stream id(4)
    const RTMP_CHUNK_TYPE_1 = 1; // 7-bytes: delta(3) + length(3) + stream type(1)
    const RTMP_CHUNK_TYPE_2 = 2; // 3-bytes: delta(3)
    const RTMP_CHUNK_TYPE_3 = 3; // 0-byte

    const RTMP_CHANNEL_PROTOCOL = 2;
    const RTMP_CHANNEL_INVOKE = 3;
    const RTMP_CHANNEL_AUDIO = 4;
    const RTMP_CHANNEL_VIDEO = 5;
    const RTMP_CHANNEL_DATA = 6;

    const rtmpHeaderSize = [11, 7, 3, 0];

    protected $handshakeState = RTMP_HANDSHAKE_UNINIT;

    protected $handshakeBytes = RTMP_HANDSHAKE_UNINIT;

    protected $handshakePayload = RTMP_HANDSHAKE_SIZE;

    /**
     * Check the integrity of the package.
     *
     * @param string  $buffer
     * @return int
     */
    protected static function input(string $buffer)
    {
        return  $buffer;
    }

    /**
     * encode.
     *
     * @param string  $payload
     * @return string
     */
    protected static function encode(string $payload)
    {
    
    }

    /**
     * decode.
     *
     * @param string   $buffer
     * @return string
     */
    protected static function decode(string $bytes)
    {
        $bytes = getBytes($bytes);
        $p = 0;
        $n = 0;
        while ($bytes > 0) {
            switch (self::$handshakeState) {
                case RTMP_HANDSHAKE_UNINIT:
                    self::$handshakeState = RTMP_HANDSHAKE_0;
                    self::$handshakeBytes = 0;
                    $bytes -= 1;
                    $p += 1;
                    break;
                case RTMP_HANDSHAKE_0:
                    $n = RTMP_HANDSHAKE_SIZE - self::$handshakeState;
                    $n = $n <= $bytes ? $n : $bytes;
                    // data.copy(this.handshakePayload, this.handshakeBytes, $p, $p + $n);
                    self::$handshakeBytes += $n;
                    $bytes -= $n;
                    $p += $n;
                    if (self::$handshakeState === RTMP_HANDSHAKE_SIZE) {
                        self::$handshakeState = RTMP_HANDSHAKE_1;
                        self::$handshakeBytes = 0;
                        // $s0s1s2 = Handshake.generateS0S1S2(self::$handshakePayload);
                        // this.socket.write(s0s1s2);
                    }
                    break;
                case RTMP_HANDSHAKE_1:
                        // Logger.log('RTMP_HANDSHAKE_1');
                        $n = RTMP_HANDSHAKE_SIZE - self::$handshakeBytes;
                        $n = $n <= $bytes ? $n : $bytes;
                        // data.copy(self::$handshakePayload, self::$handshakeBytes, $p, $n);
                        self::$handshakeBytes += $n;
                        $bytes -= $n;
                        $p += $n;
                        if (self::$handshakeBytes === RTMP_HANDSHAKE_SIZE) {
                            self::$handshakeState = RTMP_HANDSHAKE_2;
                            self::$handshakeBytes = 0;
                            self::$handshakePayload = null;
                        }
                        break;
                case RTMP_HANDSHAKE_2:
                default:
                    // Logger.log('RTMP_HANDSHAKE_2');
                    // return this.rtmpChunkRead($data, $p, $bytes);
            }
        }
    }
}