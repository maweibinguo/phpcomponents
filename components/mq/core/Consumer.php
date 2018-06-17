<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:04
 */

namespace app\components\mq\core;

use app\components\mq\rabbitmq\Channel;

class Consumer
{
    use \app\components\mq\rabbitmq\Channel;

    /**
     * 消费属性
     */
    public $_consumer_property = [
        'queue' => '',
        'consumer_tag' => '',
        'no_local' => false,
        'no_ack' => false,
        'exclusive' => false,
        'nowait' => false,
        'callback' => null,
        'ticket' => null,
        'arguments' => array()
    ];

    /**
     * 设置消费队列
     */
    public function setQueue(string $queue)
    {
        $this->_consumer_property['queue'] = $queue;
        return $this;
    }

    /**
     * 设置是否需要手动确认
     */
    public function setNoAck(bool $no_ack)
    {
        $this->_consumer_property['no_ack'] = $no_ack;
        return $this;
    }

    /**
     * 设置实际消费的业务函数
     */
    public function setCallback($callback)
    {
        $this->_consumer_property['callback'] = $callback;
        return $this;
    }

    public function consume(string $queue, $callback)
    {
        /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
        $channel = static::getChannel();
        $channel->basic_consume(
            $this->_consumer_property['queue'],
            $this->_consumer_property['consumer_tag'],
            $this->_consumer_property['no_local'],
            $this->_consumer_property['no_ack'],
            $this->_consumer_property['exclusive'],
            $this->_consumer_property['nowait'],
            $this->_consumer_property['callback'],
            $this->_consumer_property['ticket'],
            $this->_consumer_property['arguments']
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}