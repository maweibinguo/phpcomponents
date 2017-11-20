<?php
namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class NoAckMqController extends MqController
{
    /**
     * 消息不需要确认
     */  
    public function actionNoConfirmSend()
    {
        static::$channel->queue_declare('no_confirm', false, false, false, false);
        $msg = new AMQPMessage('this message dont\'t need confirm');
        static::$channel->basic_publish($msg, '', 'no_confirm');
        static::$channel->close();
        static::$connection->close();
        echo " Had Sent [x] '\n";
    }

    /**
     * 消息接收方
     */
    public function actionNoConfirmReceive()
    {
        static::$channel->queue_declare('no_confirm', false, false, false, false); 
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        //basic_consume is not blocked
        //第四个参数为true的代表数据不需要确认
        static::$channel->basic_consume('no_confirm', '', false, true, false, false, [$this, 'noconfirmCallBack']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function noconfirmCallBack($message)
    {
        //一旦进入回调函数中，消息将在队列中删除掉
        echo " [x] Received ", $message->body, "\n";
    }

    /**
     * 如果不确认消息的话，那么我们应该记录日志，通过手动补发的方式，补偿丢失的消息
     */
}
