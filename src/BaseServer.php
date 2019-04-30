<?php declare (strict_types = 1);

/*
 * @Author: try-to w@tryto.cn
 * @Date: 2019-04-30
 */
namespace TrytoMediaServer;

use TrytoMediaServer\Exceptions\BaseException;

class BaseServer extends Base
{
    protected $server;

    protected $host = '0.0.0.0';
    protected $port = 9501;
    protected $mode = null;
    protected $sock_type = null;
    protected $pid_file = null;
    protected $options = [];

    /**
     * __construct
     * @return void
     */
    public function __construct()
    {
        // 初始化
        $this->init();

        try {

            if (!empty($this->pid_file)) {
                mk_dir($this->pid_file);
            } else {
                tryto_error('ERROR:server.pid_file is empty!');
            }

            if(empty($this->host) || empty($this->port)){
                tryto_error('ERROR:server host or server port is empty!');
            }
            $this->server = new \Swoole\Server($this->host, $this->port, $this->mode, $this->sock_type);

            $this->server->set($this->options);
            $this->server->on('connect', [$this, 'onConnect']);
            $this->server->on('receive', [$this, 'onReceive']);
            $this->server->on('close', [$this, 'onClose']);
            $this->server->on('start', [$this, 'onStart']);
            $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
            $this->server->on('ManagerStart', [$this, 'onManagerStart']);
            $result = $this->server->start();
            if ($result) {
                tryto_error('WARNING: Server is shutdown!');
            } else {
                tryto_error('ERROR: Server start failed!');
            }
        } catch (\Swoole\Exception $exception) {
            tryto_error('ERROR:' . $exception->getMessage());
        } catch (\ErrorException $exception) {
            tryto_error('ERROR:' . $exception->getMessage());
        } catch (BaseException $exception) {
            tryto_error('ERROR:' . $exception->errorMessage());
        }
    }

    /**
     * init
     *
     * @return void
     */
    protected function init()
    {

    }

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
     * onStart
     *
     * @param \swoole_server $server
     * @return void
     */
    public function onStart(\swoole_server $server)
    {
        @file_put_contents($this->pid_file, $server->master_pid . ',' . $server->manager_pid);
        ProcessHelper::setProcessTitle('TrytoMediaServer master  process');
    }

    /**
     * onManagerStart
     *
     * @param \swoole_server $server
     * @return void
     */
    public function onManagerStart(\swoole_server $server)
    {
        ProcessHelper::setProcessTitle('TrytoMediaServer manager process');
    }

    /**
     * 关闭连接 销毁携程变量.
     *
     * @param $server
     * @param $fd
     */
    protected function onClose(\swoole_server $server, int $fd)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0 && isset(self::$pool[$cid])) {
            unset(self::$pool[$cid]);
        }
    }
}
