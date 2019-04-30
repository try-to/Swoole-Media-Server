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
    $parts = explode(':', $message, 2);
    $parts[0] = strtoupper($parts[0]);
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

/**
 * 获取bytes 数组
 *
 * @param $data
 *
 * @return array
 */
function getBytes(string $data)
{
    $bytes = [];
    $count = strlen($data);
    for ($i = 0; $i < $count; $i++) {
        $byte = ord($data[$i]);
        $bytes[] = $byte;
    }
    return $bytes;
}

/**
 * 获取 string.
 *
 * @param array $bytes
 *
 * @return string
 */
function getString(array $bytes)
{
    return implode(array_map('chr', $bytes));
}

/**
 * 无符号16位右移.
 *
 * @param int $x    要进行操作的数字
 * @param int $bits 右移位数
 *
 * @return int
 */
function shr16(int $x, int $bits)
{
    return ((2147483647 >> ($bits - 1)) & ($x >> $bits)) > 255 ? 255 : ((2147483647 >> ($bits - 1)) & ($x >> $bits));
}