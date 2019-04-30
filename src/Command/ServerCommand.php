<?php

namespace TrytoMediaServer\Command;

use function TrytoMediaServer\Helper\tryto_error;

class Command
{
    public $serverSetting = [];
     
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
        new \SMProxy\SMProxyServer();
    }

    /**
     * 停止服务.
     */
    public function stop()
    {

    }

    /**
     * 重启服务
     *
     * @throws \ErrorException
     * @throws \SMProxy\SMProxyException
     */
    public function restart()
    {

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
        $pFile = ROOT_PATH."/runtime/pid/server.pid";
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