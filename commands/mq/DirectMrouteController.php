<?php
namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class DirectMrouteController extends MqController
{
    public $exchange_name = 'direct_mrouting_log';

    /**
     * 直连交换机
     *
     * 直连交换机通过routeing key 进行路由, 从而确定存储消息的queue
     *
     * 这里我们将多个routing key 绑定到一个队列中，这样多个routing key的消息会被路由到同一个队列中
     */  
    public function actionDirectSend()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = $this->exchange_name, $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //设置消息持久化, 不会丢失
        $routing_key_list = [   'notice', 'error', 'fatal_error', 'exception'   ];
        $index = mt_rand(0, 3);
        $routing_key = $routing_key_list[$index];
        $message_content = ' The Message Comes From ' . $routing_key ;
        $msg = new AMQPMessage($message_content);

        //正常情况下我们应该是将消息丢到交换机中
        static::$channel->basic_publish($msg, $this->exchange_name, $routing_key);

        static::$channel->close();
        static::$connection->close();
        echo " Had Sent \r\n";
    }

    /**
     * 消息接收方
     */
    public function actionErrorLogReceive()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = 'direct_mrouting_log', $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //声明队列
        $queue_name = 'error_log';
        static::$channel->queue_declare($queue_name, false, $is_durable = false, false, false);

        //绑定队列与交换机, 此时一个队列绑定了多个key
        $routing_key_list = [   'notice', 'error', 'fatal_error'   ];
        foreach($routing_key_list as $routing_key) {
            static::$channel->queue_bind($queue_name, $this->exchange_name, $routing_key);
        }

        //basic_consume is not blocked
        static::$channel->basic_consume($queue_name, '', false, false, false, false, [$this, 'callBackErrorLog']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function callBackErrorLog($msg)
    {
        echo " [x] Received ", $msg->delivery_info['routing_key'] . '[x]' . $msg->body, "\n";
    }

    /**
     * 消息接收方
     */
    public function actionExceptionLogReceive()
    {
        //声明直连交换机
        static::$channel->exchange_declare($name = 'direct_mrouting_log', $type = 'direct', $passive = false, $durable = false, $auto_delete = false);

        //声明队列
        $queue_name = 'exception_log';
        static::$channel->queue_declare($queue_name, false, $is_durable = false, false, false);

        //绑定队列与交换机, 此时一个队列绑定了多个key
        static::$channel->queue_bind($queue_name, $this->exchange_name, 'exception');

        //basic_consume is not blocked
        static::$channel->basic_consume($queue_name, '', false, false, false, false, [$this, 'callExceptionLog']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function callExceptionLog($msg)
    {
        echo " [A] Received ", $msg->delivery_info['routing_key'] . '[A]' . $msg->body, "\n";
    }
}
