<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 16:57
 */
namespace app\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use app\components\mq\core\Producer;
use app\components\mq\core\Consumer;

class RabbitMq
{
    /**
     * 生产者
     */
    public $producer;

    /**
     * 消费者
     */
    public $consumer;

    /**
     * 初始化
     */
    public function __construct()
    {
        if(isset($this->producer)) {
            $this->producer = new Producer();
        }
        if(isset($this->consumer)) {
            $this->consumer = new Consumer();
        }
    }

    /**
     * 发送消息
     */
    public function publishDirect(string $message, $delay_time = 0)
    {
        $this->producer->publish($message, $delay_time);
    }

    /**
     * 发布消息
     */
    public function publishTopic(string $message, $delay_time)
    {

    }

    /**
     * 广播消息
     */
    public function publishFanout(string $message, $delay_time)
    {
        $this->producer->notify($message, $delay_time);
    }

    /**
     * 消费消息
     */
    public function consume(string $queue_name)
    {
        $this->consumer->consume();
    }
}