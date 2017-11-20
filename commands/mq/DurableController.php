<?php
namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class DurableController extends MqController
{
    /**
     * 消息持久化
     *
     * 必须同时设置队列和消息持久化，才能够保证消息不丢失
     */  
    public function actionDurableSend()
    {
        //设置队列持久化，不会丢失
        static::$channel->queue_declare($queue_name = 'durable_queue', false, $is_durable = true, false, false);

        //设置消息持久化, 不会丢失
        $msg = new AMQPMessage('The Message Is Durable', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        /**
         * 虽然我们使用了消息的持久化，但是mq并不保证每一条消息都不会丢失，因为将消息从内存写入磁盘是有一个时间窗口的
         */
        static::$channel->basic_publish($msg, '', 'durable_queue');
        static::$channel->close();
        static::$connection->close();
        echo " Had Sent \r\n";
    }

    /**
     * 消息接收方
     */
    public function actionDurableReceive()
    {
        static::$channel->queue_declare($queue_name = 'durable_queue', $is_durable = true, false, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        //basic_consume is not blocked
        static::$channel->basic_consume('durable_queue', '', false, false, false, false, [$this, 'durableCallBack']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function durableCallBack($message)
    {
        echo " [x] Received ", $message->body, "\n";
    }
}
