<?php

/*
 * @Author: try-to w@tryto.cn
 * @Date: 2019-04-30
 */
namespace TrytoMediaServer\Helper;

/**
 * 获取环境变量值
 * @access public
 * @param  string $name    环境变量名（支持二级 . 号分割）
 * @param  string $default 默认值
 * @return mixed
 */
function _env($name, $default = null)
{
    $result = getenv(ENV_PREFIX . strtoupper(str_replace('.', '_', $name)));
    if (false !== $result) {
        if ('false' === $result) {
            $result = false;
        } elseif ('true' === $result) {
            $result = true;
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
    if (!$prefixExists || 'ERROR' == $parts[0]) {
        exit($exitCode);
    }
}