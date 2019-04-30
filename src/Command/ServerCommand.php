<?php

namespace TrytoMediaServer\Command;

use function TrytoMediaServer\Helper\tryto_env;
use function TrytoMediaServer\Helper\tryto_error;

class ServerCommand
{
    public $logo;
    public $desc;
    public $usage;
    public $serverSetting = [];

    public function __construct()
    {
        $this->logo = HelpMessage::$logo . PHP_EOL . HelpMessage::$version;
        $this->desc = $this->logo . PHP_EOL . HelpMessage::$usage . PHP_EOL . HelpMessage::$desc;
        $this->usage = $this->logo . PHP_EOL . HelpMessage::$usage;
    }

    /**
     * 启动服务
     *
     * @throws \ErrorException
     */
    public function start()
    {
        // 是否正在运行
        if ($this->isRunning()) {
            tryto_error("The server have been running! (PID: {$this->serverSetting['masterPid']})");
        }
        echo $this->logo, PHP_EOL;
        echo 'Server starting ...', PHP_EOL;
        // new \TrytoMediaServer\MediaServer();
    }

    /**
     * 停止服务.
     */
    public function stop()
    {
        if (!$this->isRunning()) {
            tryto_error('ERROR: The server is not running! cannot stop!');
        }
        echo 'TrytoMediaServer is stopping ...', PHP_EOL;
        $result = function () {
            // 获取master进程ID
            $masterPid = $this->serverSetting['masterPid'];
            // 使用swoole_process::kill代替posix_kill
            \swoole_process::kill($masterPid);
            $timeout = 60;
            $startTime = time();
            while (true) {
                // Check the process status
                if (\swoole_process::kill($masterPid, 0)) {
                    // 判断是否超时
                    if (time() - $startTime >= $timeout) {
                        return false;
                    }
                    usleep(10000);
                    continue;
                }
                return true;
            }
        };
        // 停止失败
        if (!$result()) {
            tryto_error('TrytoMediaServer shutting down failed!');
        }
        // 删除pid文件
        @unlink(tryto_env('server.pid_file'));
        echo 'TrytoMediaServer has been shutting down.', PHP_EOL;
    }

    /**
     * 重启服务
     *
     * @throws \ErrorException
     * @throws \TrytoMediaServer\Exceptions\BaseException
     */
    public function restart()
    {
        // 是否已启动
        if ($this->isRunning()) {
            $this->stop();
        }
        // 重启默认是守护进程
        $this->start();
    }

    /**
     * 平滑重启.
     */
    public function reload()
    {
        // 是否已启动
        if (!$this->isRunning()) {
            echo 'The server is not running! cannot reload', PHP_EOL;
            return;
        }
        echo 'TrytoMediaServer is reloading...', PHP_EOL;
        \swoole_process::kill($this->serverSetting['managerPid'], SIGUSR1);
        echo 'TrytoMediaServer reload success', PHP_EOL;
    }

    /**
     * 服务状态
     */
    public function status()
    {
        // 是否已启动
        if ($this->isRunning()) {
            echo 'The server is running', PHP_EOL;
        } else {
            echo 'The server is not running', PHP_EOL;
        }
    }

    /**
     * 判断服务是否在运行中.
     *
     * @return bool
     */
    public function isRunning()
    {
        $masterIsLive = false;
        $pFile = tryto_env('server.pid_file');
        // 判断pid文件是否否存在
        if (file_exists($pFile)) {
            // 获取pid文件内容
            $pidFile = file_get_contents($pFile);
            $pids = explode(',', $pidFile);
            $this->serverSetting['masterPid'] = $pids[0];
            $this->serverSetting['managerPid'] = $pids[1];
            $masterIsLive = $this->serverSetting['masterPid'] && @\swoole_process::kill($this->serverSetting['managerPid'], 0);
        }
        return $masterIsLive;
    }
}
