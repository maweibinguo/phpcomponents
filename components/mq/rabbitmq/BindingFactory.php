<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/10
 * Time: 15:00
 */

namespace app\components\mq\rabbitmq;


class BindingFactory
{
    use Channel;

    /**
     * 工厂实例
     */
    public static $instance;

    /**
     * 绑定对象
     */
    public static $binding_instances = [];

    /**
     * 绑定属性
     */
    private $_binding_property = [
        'queue' => 'direct_queue',
        'exchange' => 'direct_exchange',
        'routing_key' => 'fuck',
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    ];

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
     * 绑定
     */
    public function bind()
    {
        $hash = md5(serialize($this->_binding_property));
        if (isset(static::$binding_instances[$hash])) {
            return true;
        }
        try{
            $channel = Channel::getChannel();
            $channel->queue_bind(
                $this->_binding_property['queue'],
                $this->_binding_property['exchange'],
                $this->_binding_property['routing_key'],
                $this->_binding_property['nowait'],
                $this->_binding_property['arguments'],
                $this->_binding_property['ticket']
            );
            static::$binding_instances[$hash] = $this->_binding_property['exchange'] .'.' .$this->_binding_property['queue'];
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取工厂对象
     */
    public static function getInstance(bool $is_reuse = false)
    {
        if (static::$instance instanceof self && $is_reuse === true) {
            return static::$instance;
        } else {
            static::$instance = new static();
            return static::$instance;
        }
    }
}