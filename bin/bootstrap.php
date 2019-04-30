<?php

use function TrytoMediaServer\Helper\tryto_error;

defined('IN_PHAR') or define('IN_PHAR', boolval(Phar::running(false)));
defined('ROOT_PATH') or define('ROOT_PATH', IN_PHAR ? dirname(Phar::running(false)) : realpath(__DIR__ . '/..'));
defined('TRYTO_VERSION') or define('TRYTO_VERSION', '0.0.1');

// Composer autoload
require_once ROOT_PATH . '/vendor/autoload.php';

// 加载环境变量配置文件
if (is_file(ROOT_PATH . '/.env')) {
    $env = parse_ini_file(ROOT_PATH . '/.env', true, INI_SCANNER_RAW);
    $config = [];
    foreach ($env as $key => $val) {
        $name = strtoupper($key);
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $item = $name . '_' . strtoupper($k);
                $config[$item] = $v;
            }
        } else {
            $config[$name] = $val;
        }
    }
    defined('TRYTO_CONF') or define('TRYTO_CONF', $config);
} else {
    tryto_error('ERROR: .env 配置项不存在!');
}

// Check requirements - PHP
if (version_compare(PHP_VERSION, '7.0', '<')) {
    tryto_error("ERROR: TrytoMediaServer requires [PHP >= 7.0].");
}

// Check requirements - Swoole
if (extension_loaded('swoole') && defined('SWOOLE_VERSION')) {
    if (version_compare(SWOOLE_VERSION, '4.1', '<')) {
        tryto_error("ERROR: TrytoMediaServer requires [Swoole >= 4.1].");
    }
} else {
    tryto_error("ERROR: Swoole was not installed.");
}
