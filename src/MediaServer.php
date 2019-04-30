<?php declare(strict_types=1);

/*
 * @Author: try-to w@tryto.cn
 * @Date: 2019-04-30
 */
namespace TrytoMediaServer;

class MediaServer extends BaseServer
{
    /**
     * onConnect
     *
     * @param \swoole_server $server
     * @param integer $fd
     * @return void
     */
    protected function onConnect(\swoole_server $server, int $fd)
    {

    }
    
    /**
     * onReceive
     *
     * @param \swoole_server $server
     * @param integer $fd
     * @param integer $reactor_id
     * @param string $data
     * @return void
     */
    protected function onReceive(\swoole_server $server, int $fd, int $reactor_id, string $data)
    {

    }

    /**
     * onWorkerStart
     *
     * @param \swoole_server $server
     * @param integer $worker_id
     * @return void
     */
    protected function onWorkerStart(\swoole_server $server, int $worker_id)
    {

    }

    /**
     * 关闭连接 销毁携程变量.
     *
     * @param $server
     * @param $fd
     */
    protected function onClose(\swoole_server $server, int $fd)
    {
        parent::onClose($server, $fd);
    }
}