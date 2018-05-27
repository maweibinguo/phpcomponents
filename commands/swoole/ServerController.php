<?php 
namespace app\commands\swoole;

use yii\console\Controller;

class ServerController extends Controller
{
    /**
     * server
     */
    public $server;

    /**
     * 初始化swoole
     */
    public function init()
    {
        $this->server = new \swoole_server("127.0.0.1", 9501);
        $this->server->set(['worker_num' => 2]);
    }

    public function actionListen()
    {
        $this->server->on('Connect', function($server, $fd){
            echo "new client connected ." . PHP_EOL;
        });
        $this->server->on('Receive', function($server, $fd, $fromId, $data){
            $this->server->send($fd, 'Server' . $data); 
        });
        $this->server->on('Close', function($server, $fd){
            echo "Client close . " . PHP_EOL; 
        });

        //这个地方进行了阻塞
        $this->server->start();
    }

    public function actionTask()
    {
        /**
         * 设置task进程
         */
        $this->server->set(['task_worker_num' => 1]);

        $this->server->on('Connect', function($server, $fd){
            echo "new client connected ." . PHP_EOL;
        });
        $this->server->on('Receive', function($server, $fd, $fromId, $data){
            echo "worker received data: {$data}" . PHP_EOL;
            $this->server->task($data);
            $this->server->send($fd, 'This is a message from server.'); 
            echo "worker continue run."  . PHP_EOL;
        });

        /**
         * $serv swoole_server
         * $taskId 投递的任务id,因为task进程是由worker进程发起，所以多worker多task下，该值可能会相同
         * $fromId 来自那个worker进程的id
         * $data 要投递的任务数据
         */
        $this->server->on('Task',function($serv, $taskId, $fromId, $data){
            echo "task start. --- from worker id: {$fromId}." . PHP_EOL; 
            for ($i=0; $i < 5; $i++) { 
                sleep(1);
                echo "task runing. --- {$i}" . PHP_EOL;
            }
            return "task end." . PHP_EOL;
        });

        /**
         * 注册finish 就算不触发，也必须定义否则会报错
         */
        $this->server->on('Finish', function($serv, $taskId, $data){
            echo "finish received data '{$data}'" . PHP_EOL;
        });

        $this->server->on('Close', function($server, $fd){
            echo "Client close . " . PHP_EOL; 
        });

        //这个地方进行了阻塞
        $this->server->start();
    }

    /**
     * 进行模型
     */
    public function actionProcess()
    {
        /**
         * 设置task进程
         */
        $this->server->set(['task_worker_num' => 1]);

        $this->server->on('Connect', function($server, $fd){
            echo "new client connected ." . PHP_EOL;
        });

        $this->server->on('Receive', function($server, $fd, $fromId, $data){
            echo "worker received data: {$data}" . PHP_EOL;
            $this->server->task($data);
            $this->server->send($fd, 'This is a message from server.'); 
            echo "worker continue run."  . PHP_EOL;
        });

        /**
         * $serv swoole_server
         * $taskId 投递的任务id,因为task进程是由worker进程发起，所以多worker多task下，该值可能会相同
         * $fromId 来自那个worker进程的id
         * $data 要投递的任务数据
         */
        $this->server->on('Task',function($serv, $taskId, $fromId, $data){
            echo "task start. --- from worker id: {$fromId}." . PHP_EOL; 
            for ($i=0; $i < 5; $i++) { 
                sleep(1);
                echo "task runing. --- {$i}" . PHP_EOL;
            }
            return "task end." . PHP_EOL;
        });

        /**
         * 注册finish 就算不触发，也必须定义否则会报错
         */
        $this->server->on('Finish', function($serv, $taskId, $data){
            echo "finish received data '{$data}'" . PHP_EOL;
        });

        $this->server->on('Close', function($server, $fd){
            echo "Client close . " . PHP_EOL; 
        });

        $this->server->on('start', function($server){
            swoole_set_process_name('server-process:master');
        });

        $this->server->on('ManagerStart', function($server){
            swoole_set_process_name('server-process:manager');
        });

        $this->server->on('WorkerStart', function($server, $worker_id){
            if($worker_id >= 2) {
                swoole_set_process_name('server-process:task');
            } else {
                swoole_set_process_name('server-process:worker');
            }
        });

        //这个地方进行了阻塞
        $this->server->start();
    }
}
