<?php
namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class RpcController extends MqController
{

    /**
     * 交换机
     */
    public $exchange_name = 'rpc_exchange';

    /**
     * rpc queue
     */
    public $queue = 'rpc_queue';


    public function fib($n) 
    {
        if ($n == 0)
            return 0;
        if ($n == 1)
            return 1;
        return $this->fib($n-1) + $this->fib($n-2);
    } 

    /**
     * 请求回调
     */
    public function requestCallBack($req)
    {
        $n = intval($req->body);
        echo " [.] fib(", $n, ")\n";

        $msg = new AMQPMessage(
            (string) $this->fib($n),
            array('correlation_id' => $req->get('correlation_id'))
            );

        $req->delivery_info['channel']->basic_publish(
            $msg, '', $req->get('reply_to'));
        $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }

    /**
     * 广播交换机
     *
     * 我们将消息广播到所有绑定的队列上 
     */  
    public function actionRequest()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = $this->exchange_name, $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //生命rpc queue
        static::$channel->queue_declare($this->queue, false, false, false, false);

        static::$channel->basic_qos(null, 1, null);
        static::$channel->basic_consume($this->queue, '', false, false, false, false, [$this, 'requestCallBack']);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        static::$channel->close();
        static::$connection->close();
    }

    /**
     * 消息接收方
     */
    public function actionFanoutReceive()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = $this->exchange_name, $type = 'fanout', $passive = false, $durable = false, $auto_delete = false);

        //声明队列
        $queue_name_list = [
            'fanout_queue_one',
            'fanout_queue_two',
            'fanout_queue_three'
        ];
        foreach($queue_name_list as $queue_name) {
            static::$channel->queue_declare($queue_name, false, $is_durable = false, false, false);

            //绑定队列与交换机
            static::$channel->queue_bind($queue_name, $exchange = $this->exchange_name);

            //basic_consume is not blocked
            static::$channel->basic_consume($queue_name, '', false, true, false, false, [$this, 'callBack']);
        }

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function callBack($msg)
    {
        echo " [x] Received ", $msg->delivery_info['routing_key'] . '[x]' . $msg->body, "\n";
    }

}
