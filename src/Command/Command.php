<?php

namespace TrytoMediaServer\Command;

use function TrytoMediaServer\Helper\mk_dir;
use function TrytoMediaServer\Helper\tryto_env;
use function TrytoMediaServer\Helper\tryto_error;
use TrytoMediaServer\Helper\PhpHelper;

class Command
{
    /**
     * 运行
     *
     * @param array $argv
     *
     * @throws \TrytoMediaServer\Exceptions\BaseException
     */
    public function run(array $argv)
    {
        // 一键协程化
        \Swoole\Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_FILE);

        $command = count($argv) >= 2 ? $argv[1] : false;
        $this->commandHandler($command);
    }

    /**
     * 处理命令
     *
     * @param string $command
     */
    protected function commandHandler(string $command)
    {
        $serverCommand = new ServerCommand();
        if ('-h' == $command || '--help' == $command) {
            echo $serverCommand->desc, PHP_EOL;
            return;
        }
        if ('-v' == $command || '--version' == $command) {
            echo $serverCommand->logo, PHP_EOL;
            return;
        }
        if (!$command || !method_exists($serverCommand, $command)) {
            echo $serverCommand->usage, PHP_EOL;
            return;
        }
        PhpHelper::call([$serverCommand, $command]);
    }
}
