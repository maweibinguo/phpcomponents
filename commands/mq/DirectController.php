<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/24
 * Time: 15:02
 */

namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class DirectController extends MqController
{
    /**
     * 发送消息Direct
     */
    public function actionDirectSend()
    {
        /**
         * 声明交换机
         */
        static::$channel->exchange_declare(
            $exchange = 'direct_exchange',
            $type = 'fanout',
            $passive = false,
            $durable = false,
            $auto_delete = true,
            $internal = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        /**
         * 声明队列
         */
        static::$channel->queue_declare($queue_name = 'direct_queue',
                                        $passive = false,
                                        $is_durable = true,
                                        $exclusive = false,
                                        $auto_delete = false,
                                        $no_wait = false,
                                        $arguments = null,
                                        $ticket = null);

        /**
         * 将交换机同队列进行绑定,并决定routing_key
         *
         * 这个routing_key用来同消息的routing_key进行匹配使用:
         * direct(直连交换机)要完全匹配才可以;
         * topic(主题交换机)可以通过特殊字符进行匹配,
         */
        static::$channel->queue_bind(
            $queue = 'direct_queue',
            $exchange = 'direct_exchange',
            $routing_key = 'fuck',
            $nowait = false,
            $arguments = null,
            $ticket = null
        );
        $task = serialize(new Task());
        $msg = new AMQPMessage($task);

        /**
         * routing_key只对direcct(直连交换机)，topic（主题交换机）管用，fanout（广播交换没有任何影响）
         *
         * 第一步、当我们发布消息到交换机的时候，消息会有一个routing_key,就是下面的参数。
         * 第二步、交换机需要将消息路由到队列中，路由的方式其实就是，将消息的routing_key 同 消息队列同交换机绑定的routing_key作比较
         * 第三步、如果消息的routing_key同队列与交换机绑定的routing_key相匹配，那么该条消息就会被路由到该队列
         */
        $result = static::$channel->basic_publish(
            $msg,
            $exchange = 'direct_exchange',
            $routing_key = 'fuck',//这个地方其实就是消息路由的key
            $mandatory = false,
            $immediate = false,
            $ticket = null
        );
        echo "[x] message hand send" . PHP_EOL;
        static::$channel->close();
    }

    /**
     * direct获取消息
     */
    public function actionDirectGet()
    {
        /**
         * 声明交换机
         */
        static::$channel->exchange_declare(
            $exchange = 'direct_exchange',
            $type = 'direct',
            $passive = false,
            $durable = false,
            $auto_delete = true,
            $internal = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        /**
         * 声明队列
         */
        static::$channel->queue_declare($queue_name = 'direct_queue',
            $passive = false,
            $is_durable = true,
            $exclusive = false,
            $auto_delete = false,
            $no_wait = false,
            $arguments = null,
            $ticket = null);

        /**
         * 将交换机同队列进行绑定,并决定routing_key
         */
        static::$channel->queue_bind(
            $queue = 'direct_queue',
            $exchange = 'direct_exchange',
            $routing_key = 'fuck',
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        static::$channel->basic_consume(
                                        $queue = 'direct_queue',
                                        $consumer_tag = '',
                                        $no_local = false,
                                        $no_ack = true,
                                        $exclusive = false,
                                        $nowait = false,
                                        $callback = [$this, 'directCallBack'],
                                        $ticket = null,
                                        $arguments = array()
                                        );

        while(count(static::$channel->callbacks)) {
            //这里才是真正会阻塞的地方
            static::$channel->wait();
        }
    }

    /**
     * 直连队列回调
     */
    public function directCallBack($message)
    {
        $obj = unserialize($message->body);
    }

    /**
     * 发送ttl消息
     */
    public function actionSendTtl()
    {
        /**
         * 声明交换机
         */
        static::$channel->exchange_declare(
            $exchange = 'direct_exchange',
            $type = 'direct',
            $passive = false,
            $durable = false,
            $auto_delete = false,
            $internal = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        /**
         * 声明队列
         */
        static::$channel->queue_declare($queue_name = 'direct_queue',
            $passive = false,
            $is_durable = true,
            $exclusive = false,
            $auto_delete = false,
            $no_wait = false,
            $arguments = null,
            $ticket = null);

        /**
         * 将交换机同队列进行绑定,并决定routing_key
         *
         * 这个routing_key用来同消息的routing_key进行匹配使用:
         * direct(直连交换机)要完全匹配才可以;
         * topic(主题交换机)可以通过特殊字符进行匹配,
         */
        static::$channel->queue_bind(
            $queue = 'direct_queue',
            $exchange = 'direct_exchange',
            $routing_key = 'fuck',
            $nowait = false,
            $arguments = null,
            $ticket = null
        );
        $task = 'fuck you';
        $msg = new AMQPMessage($task, ['expiration' => 10000]);

        static::$channel->basic_publish(
            $msg,
            $exchange = 'direct_exchange',
            $routing_key = 'fuck',//这个地方其实就是消息路由的key
            $mandatory = false,
            $immediate = false,
            $ticket = null
        );
        echo "[x] message hand send" . PHP_EOL;
        static::$channel->close();

    }


}
