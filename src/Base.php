<?php declare(strict_types=1);

namespace TrytoMediaServer;

use TrytoMediaServer\Exceptions\BaseException;

class Base extends Context
{
    /**
     * 携程执行处理异常.
     *
     * @param $function
     */
    protected static function go(\Closure $function)
    {
        if (-1 !== \Swoole\Coroutine::getuid()) {
            $pool = self::$pool[\Swoole\Coroutine::getuid()] ?? false;
        } else {
            $pool = false;
        }
        go(function () use ($function, $pool) {
            try {
                if ($pool) {
                    self::$pool[\Swoole\Coroutine::getuid()] = $pool;
                }
                $function();
                if ($pool) {
                    unset(self::$pool[\Swoole\Coroutine::getuid()]);
                }
            } catch (BaseException $BaseException) {
                self::writeErrorMessage($BaseException, 'system');
            }
        });
    }

    /**
     * 写入日志
     *
     * @param $exception
     * @param string $tag
     */
    protected static function writeErrorMessage($exception, string $tag = 'system')
    {
        $errLevel = $exception ->getCode() ? array_search($exception ->getCode(), Log::$levels) : 'warning';
        echo  '[' . ucfirst($errLevel) . '] ', $exception->errorMessage(), PHP_EOL;
    }
}