<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/10
 * Time: 15:00
 */

namespace app\components\mq\rabbitmq;


class Binding
{
    use Channel;

    /**
     * 绑定实例
     */
    public static $instances = [];

    /**
     * 绑定属性
     */
    private $_binding_property = [
        'queue' => '',
        'exchange' => '',
        'routing_key' => '',
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    ];

    /**
     * 构造函数
     */
    private function __construct(string $queue, string $exchange, string $binding_key)
    {
        $this->setQueue($queue);
        $this->setExchange($exchange);
        $this->setRoutingKey($binding_key);
    }

    /**
     * 设置队列名称
     */
    public function setQueue(string $queue)
    {
        $this->_binding_property['queue'] = $queue;
        return $this;
    }

    /**
     * 设置交换机
     */
    public function setExchange(string $exchange)
    {
        $this->_binding_property['exchange'] = $exchange;
        return $this;
    }

    /**
     * 设置路由键
     */
    public function setRoutingKey(string $routing_key)
    {
        $this->_binding_property['routing_key'] = $routing_key;
        return $this;
    }

    /**
     * 是否等待
     */
    public function setNowait(bool $nowait)
    {
        $this->_binding_property['nowait'] = $nowait;
        return $this;
    }

    /**
     * 绑定
     */
    public function bind()
    {
        /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
        $channel = Channel::getChannel();
        $channel->queue_bind(
            $this->_binding_property['queue'],
            $this->_binding_property['exchange'],
            $this->_binding_property['routing_key'],
            $this->_binding_property['nowait'],
            $this->_binding_property['arguments'],
            $this->_binding_property['ticket']
        );
    }

    /**
     * 获取实例
     */
    public static function getInstance(string $queue, string $exchange, string $binding_key)
    {
        $flag = implode('-', [$queue, $exchange, $binding_key]);
        if (isset(static::$instances[$flag]) && static::$instances[$flag] instanceof static) {
            return static::$instances[$flag];
        } else {
            static::$instances[$flag] = new static($queue, $exchange, $binding_key);
            return static::$instances[$flag];
        }
    }
}