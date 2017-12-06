<?php
namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class DirectController extends MqController
{
    /**
     * 直连交换机
     *
     * 直连交换机通过routeing key 进行路由, 从而确定存储消息的queue
     *
     * 这里我们将消息投递到多个队列中, 有点像广播，但是前提是绑定的queue
     */  
    public function actionDirectSend()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = 'direct_log', $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //设置消息持久化, 不会丢失
        $message_content = ' Warning Message ' . mt_rand();
        $msg = new AMQPMessage($message_content);

        //正常情况下我们应该是将消息丢到交换机中
        static::$channel->basic_publish($msg, $exchange = 'direct_log', $routing_key = 'warning_error');

        static::$channel->close();
        static::$connection->close();
        echo " Had Sent \r\n";
    }

    /**
     * 消息接收方
     */
    public function actionWriteLogReceive()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = 'direct_log', $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //声明队列
        $queue_name = 'write_log';
        static::$channel->queue_declare($queue_name, false, $is_durable = false, false, false);

        //绑定队列与交换机
        static::$channel->queue_bind($queue_name, $exchange = 'direct_log', $routing_key = 'warning_error');

        //basic_consume is not blocked
        static::$channel->basic_consume($queue_name, '', false, false, false, false, [$this, 'callBackWriteLog']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function callBackWriteLog($msg)
    {
        echo " [x] Received ", $msg->delivery_info['routing_key'] . '[x]' . $msg->body, "\n";
    }

    /**
     * 消息接收方
     */
    public function actionAnalysizeReceive()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = 'direct_log', $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //声明队列
        $queue_name = 'analysize_log';
        static::$channel->queue_declare($queue_name, false, $is_durable = false, false, false);

        //绑定队列与交换机
        static::$channel->queue_bind($queue_name, $exchange = 'direct_log', $routing_key = 'warning_error');

        //basic_consume is not blocked
        static::$channel->basic_consume($queue_name, '', false, false, false, false, [$this, 'callBackAnalysize']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function callBackAnalysize($msg)
    {
        echo " [A] Received ", $msg->delivery_info['routing_key'] . '[A]' . $msg->body, "\n";
    }
}
