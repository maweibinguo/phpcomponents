<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/22
 * Time: 20:57
 */

namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class TtlController extends MqController
{
    /**
     * 消息延迟发布
     *
     * 经过实验发现，确实可以起到消息延迟的作用
     */
    public function actionDelaySend($queue_name, $delay_time)
    {
        //声明延迟交换机
        $delay_exchange = 'delay_exchange';
        static::$channel->exchange_declare(
                                            $exchange = $delay_exchange,
                                            $type = 'direct',
                                            $passive = false,
                                            $durable = false,
                                            $auto_delete = false,
                                            $internal = false,
                                            $nowait = false,
                                            $arguments = null,
                                            $ticket = null
                                            );

        //声明业务交换机
        $business_exchange = 'business_exchange';
        static::$channel->exchange_declare(
            $exchange = $business_exchange,
            $type = 'direct',
            $passive = false,
            $durable = false,
            $auto_delete = false,
            $internal = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );


        //声明延迟队列
        $delay_queue = 'delay_queue';
        $table = new AMQPTable();
        //通过x-dead-letter-exhange 我们可以指定当这个队列中的消息处于死信状态时，将会被投递到那个交换机
        $table->set('x-dead-letter-exchange', $business_exchange);
        //重新设置投的死信中的routing-key，而非绑定的routing-key
        $table->set('x-dead-letter-routing-key', $queue_name);
        static::$channel->queue_declare(
                                        $queue = $delay_queue,
                                        $passive = false,
                                        $durable = false,
                                        $exclusive = false,
                                        $auto_delete = false,
                                        $nowait = false,
                                        $arguments = $table,
                                        $ticket = null
                                        );

        //声明真正要消费的队列
        static::$channel->queue_declare(
                                        $queue = $queue_name,
                                        $passive = false,
                                        $durable = false,
                                        $exclusive = false,
                                        $auto_delete = false,
                                        $nowait = false,
                                        $arguments = null,
                                        $ticket = null
        );

        //绑定延迟交换机和延迟队列
        static::$channel->queue_bind(   $queue = $delay_queue,
                                        $exchange = $delay_exchange,
                                        $routing_key = $delay_queue,
                                        $nowait = false,
                                        $arguments = null,
                                        $ticket = null  );

        //将过期的消息投递到business_exchange 交换机中，交换机（direct）需要路由到对应的队列中，需要参考两个键：消息键，绑定的路邮键
        //绑定业务交换机和业务队列
        static::$channel->queue_bind(   $queue = $queue_name,
                                        $exchange = $business_exchange,
                                        $routing_key = $queue_name,
                                        $nowait = false,
                                        $arguments = null,
                                        $ticket = null  );

        //将消息发布到队列中
        $message = "this is a delay message";
        $mq_msg = new AMQPMessage($message, [
           'expiration' => $delay_time
        ]);
        static::$channel->basic_publish(
                                        $msg = $mq_msg,
                                        $exchange = $delay_exchange,
                                        $routing_key = $delay_queue,
                                        $mandatory = false,
                                        $immediate = false,
                                        $ticket = null
                                        );
    }

    /**
     * 拉取消费
     */
    public function actionPull()
    {
        /**
         * 声明交换机
         */
        static::$channel->exchange_declare(
            $exchange = 'business_exchange',
            $type = 'direct',
            $passive = false,
            $durable = false,
            $auto_delete = false,
            $internal = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        //声明真正要消费的队列
        static::$channel->queue_declare(
            $queue = 'business_queue',
            $passive = false,
            $durable = false,
            $exclusive = false,
            $auto_delete = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        /**
         * 将交换机同队列进行绑定,并决定routing_key
         */
        static::$channel->queue_bind(
            $queue = 'business_queue',
            $exchange = 'business_exchange',
            $routing_key = 'business_queue',
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        static::$channel->basic_consume(
            $queue = 'business_queue',
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
        sleep(60);
        $obj = unserialize($message->body);
        var_dump($obj);
    }
}