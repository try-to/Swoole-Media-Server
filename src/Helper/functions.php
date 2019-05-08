<?php

/*
 * @Author: try-to w@tryto.cn
 * @Date: 2019-04-30
 */
namespace TrytoMediaServer\Helper;

/**
 * 创建目录.
 *
 * @param string $path
 */
function mk_dir(string &$path)
{
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
}

/**
 * 替换常量值
 *
 * @param string $const
 * @param string $default
 */
function replace_constant(string &$const, string $default = '')
{
    if (defined($const)) {
        $const = constant($const);
    } else {
        $const = $default;
    }
}

/**
 * 获取服务类型
 *  SWOOLE_BASE 使用Base模式，业务代码在Reactor进程中直接执行
    SWOOLE_PROCESS 使用进程模式，业务代码在Worker进程中执行
 * @param string $sock_type
 */
function get_mode($mode)
{
    $type = '';
    switch ($mode) {
        case SWOOLE_BASE:
            $type = 'Base模式';
            break;
        case SWOOLE_PROCESS:
            $type = '进程模式';
            break;
            break;
        default:
            $type = 'none';
            break;
    }
    return $type;
}

/**
 * 获取服务类型
    SWOOLE_SOCK_TCP 创建tcp socket
    SWOOLE_SOCK_TCP6 创建tcp ipv6 socket
    SWOOLE_SOCK_UDP 创建udp socket
    SWOOLE_SOCK_UDP6 创建udp ipv6 socket
    SWOOLE_SOCK_UNIX_DGRAM 创建unix dgram socket
    SWOOLE_SOCK_UNIX_STREAM 创建unix stream socket
    SWOOLE_SOCK_SYNC 同步客户端
    SWOOLE_SOCK_ASYNC 异步客户端
 *
 * @param string $sock_type
 */
function get_sock_type($sock_type)
{
    $type = '';
    switch ($sock_type) {
        case SWOOLE_SOCK_TCP:
            $type = 'tcp';
            break;
        case SWOOLE_SOCK_TCP6:
            $type = 'tcp ipv6';
            break;
        case SWOOLE_SOCK_UDP:
            $type = 'udp';
            break;
        case SWOOLE_SOCK_UDP6:
            $type = 'udp ipv6';
            break;
        case SWOOLE_SOCK_UNIX_DGRAM:
            $type = 'dgram';
            break;
        case SWOOLE_SOCK_UNIX_STREAM:
            $type = 'stream';
            break;
        default:
            $type = 'none';
            break;
    }
    return $type;
}

/**
 * 获取环境变量值
 * @access public
 * @param  string $name    环境变量名（支持二级 . 号分割）
 * @param  string $default 默认值
 * @return mixed
 */
function tryto_env($name, $default = null)
{
    $result = false;
    if (isset(TRYTO_CONF[strtoupper(str_replace('.', '_', $name))])) {
        $result = TRYTO_CONF[strtoupper(str_replace('.', '_', $name))];
    }
    if (false !== $result) {
        if ('false' === $result) {
            $result = false;
        } elseif ('true' === $result) {
            $result = true;
        } else {
            // 常量转换
            $result = str_replace(['ROOT_PATH'], [ROOT_PATH], $result);

            //替换swoole 常量
            if ($result == 'SWOOLE_PROCESS') {
                $result = SWOOLE_PROCESS;
            }

            if ($result == 'SWOOLE_SOCK_TCP') {
                $result = SWOOLE_SOCK_TCP;
            }
        }
        return $result;
    }
    return $default;
}

/**
 * tryto_error.
 *
 * @param $message
 * @param int $exitCode
 */
function tryto_error($message, $exitCode = 0)
{
    $parts        = explode(':', $message, 2);
    $parts[0]     = strtoupper($parts[0]);
    $prefixExists = in_array($parts[0], [
        'ERROR', 'WARNING', 'NOTICE',
    ]);
    if ($prefixExists) {
        $message = $parts[0] . ': ' . trim($parts[1]);
    } else {
        $message = 'ERROR: ' . $message;
    }
    echo $message . PHP_EOL;
    exit($exitCode);
}

/**
 * 数组复制.
 *
 * @param $array
 * @param $start
 * @param $len
 *
 * @return array
 */
function array_copy(array $array, int $start, int $len)
{
    return array_slice($array, $start, $len);
}
