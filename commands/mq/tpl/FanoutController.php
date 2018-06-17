<?php

namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class FanoutController extends MqController
{

    /**
     * 交换机
     */
    public $exchange_name = 'fanout_log';

    /**
     * 广播交换机
     *
     * 我们将消息广播到所有绑定的队列上
     */
    public function actionFanoutSend()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = $this->exchange_name, $type = 'fanout', $passive = false, $durable = false, $auto_delete = false);

        //设置消息持久化, 不会丢失
        $message_content = ' Fanout Message ' . mt_rand();
        $msg = new AMQPMessage($message_content);

        //正常情况下我们应该是将消息丢到交换机中
        static::$channel->basic_publish($msg, $exchange = $this->exchange_name);

        static::$channel->close();
        static::$connection->close();
        echo " Had Sent \r\n";
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
        foreach ($queue_name_list as $queue_name) {
            static::$channel->queue_declare($queue_name, false, $isdurable = false, false, false);

            //绑定队列与交换机
            static::$channel->queue_bind($queue_name, $exchange = $this->exchange_name);

            //basic_consume is not blocked
            static::$channel->basic_consume($queue_name, '', false, true, false, false, [$this, 'callBack']);
        }

        //callbacks is block
        while (count(static::$channel->callbacks)) {
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
