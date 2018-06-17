<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/31
 * Time: 22:46
 */

namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class NamelessController extends MqController
{
    /**
     * 生产者
     */
    public function actionSend()
    {
        $msg = new AMQPMessage('this is a test');
        static::$channel->basic_publish(
            $msg,
            $exchange = '',
            $routing_key = '*',//这个地方其实就是消息路由的key
            $mandatory = false,
            $immediate = false,
            $ticket = null
        );
        echo "[x] message hand send" . PHP_EOL;
        static::$channel->close();
    }

    /**
     * 消费者1
     */
    public function actionConsumer1()
    {
        static::$channel->queue_declare('consumer_one', false, false, false, false);
        static::$channel->basic_consume(
            $queue = 'consumer_one',
            $consumer_tag = '',
            $no_local = false,
            $no_ack = true,
            $exclusive = false,
            $nowait = false,
            $callback = [$this, 'consumerOne'],
            $ticket = null,
            $arguments = array()
        );

        while(count(static::$channel->callbacks)) {
            //这里才是真正会阻塞的地方
            static::$channel->wait();
        }
    }

    public function consumerOne($mq)
    {
        var_dump('[consumer one]' . $mq->body);
    }

    /**
     * 消费者2
     */
    public function actionConsumer2()
    {
        static::$channel->queue_declare('consumer_two', false, false, false, false);
        static::$channel->basic_consume(
            $queue = 'consumer_two',
            $consumer_tag = '',
            $no_local = false,
            $no_ack = true,
            $exclusive = false,
            $nowait = false,
            $callback = [$this, 'consumerTwo'],
            $ticket = null,
            $arguments = array()
        );

        while(count(static::$channel->callbacks)) {
            //这里才是真正会阻塞的地方
            static::$channel->wait();
        }
    }

    public function consumerTwo($mq)
    {
        var_dump('[consumer two]' . $mq->body);
    }
}