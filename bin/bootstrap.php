<?php

use function TrytoMediaServer\Helper\tryto_error;

defined('IN_PHAR') or define('IN_PHAR', boolval(Phar::running(false)));
defined('ROOT_PATH') or define('ROOT_PATH', IN_PHAR ? dirname(Phar::running(false)) : realpath(__DIR__ . '/..'));
defined('ENV_PREFIX') or define('ENV_PREFIX', 'TRYTO_'); // 环境变量的配置前缀

// Composer autoload
require_once ROOT_PATH . '/../vendor/autoload.php';

// Check requirements - PHP
if (version_compare(PHP_VERSION, '7.0', '<')) {
    tryto_error("ERROR: TrytoMediaServer requires [PHP >= 7.0].");
}

// Check requirements - Swoole
if (extension_loaded('swoole') && defined('SWOOLE_VERSION')) {
    if (version_compare(SWOOLE_VERSION, '4.0', '<')) {
        tryto_error("ERROR: TrytoMediaServer requires [Swoole >= 4.0].");
    }
} else {
    tryto_error("ERROR: Swoole was not installed.");
}

// 加载环境变量配置文件
if (is_file(ROOT_PATH . '/../.env')) {
    $env = parse_ini_file(ROOT_PATH . '/../.env', true);
    foreach ($env as $key => $val) {
       $name = ENV_PREFIX . strtoupper($key);
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $item = $name . '_' . strtoupper($k);
                putenv("$item=$v");
            }
        } else {
            putenv("$name=$val");
        }
    }
}
//\Swoole\Runtime::enableCoroutine();
//Swoole\Coroutine::set([
//    'max_coroutine' => 300000,
//]);
