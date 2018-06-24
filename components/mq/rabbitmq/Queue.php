<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:05
 */

namespace app\components\mq\rabbitmq;

use PhpAmqpLib\Wire\AMQPTable;

class Queue
{
    use Channel;
    use DeadLetter;

    /**
     * 设置队列属性
     */
    public $_queue_property = [
        'queue' => '',
        'passive' => false,
        'durable' => true,
        'exclusive' => false,
        'auto_delete' => false,
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    ];

    /**
     * 队列实例
     */
    public static $instances = [];

    /**
     * 构造函数
     */
    public function __construct(string $name)
    {
        $this->setQueue($name);
    }

    /**
     * 获取队列名称
     */
    public function getQueue(): string
    {
        return $this->_queue_property['queue'];
    }

    /**
     * 设置队列名称
     */

    public function setQueue(string $queue)
    {
        $this->_queue_property['queue'] = $queue;
        return $this;
    }

    /**
     * 设置是否持久化
     */
    public function setIsDurable(bool $durable)
    {
        $this->_queue_property['durable'] = $durable;
        return $this;
    }

    /**
     * 设置是否是独占队列
     */
    public function setAutoDelete(bool $auto_delete)
    {
        $this->_queue_property['auto_delete'] = $auto_delete;
        return $this;
    }

    /**
     * 设置队列属性
     */
    public function setArguments(AMQPTable $table)
    {
        $this->_queue_property['arguments'] = $table;
        return $this;
    }

    /**
     * 创建队列
     */
    public function declare()
    {
        /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
        $channel = static::getChannel();
        $channel->queue_declare(
            $this->_queue_property['queue'],
            $this->_queue_property['passive'],
            $this->_queue_property['durable'],
            $this->_queue_property['exclusive'],
            $this->_queue_property['auto_delete'],
            $this->_queue_property['nowait'],
            $this->_queue_property['arguments'],
            $this->_queue_property['ticket']
        );
    }

    /**
     * 创建延迟队列
     */
    public function declareDelay(string $exchange, string $routing_key, int $delay_seconds = 5)
    {
        $table = new AMQPTable();
        $table->set('x-dead-letter-exchange', $exchange);
        $table->set('x-dead-letter-routing-key', $routing_key);
        $table->set('x-message-ttl', $delay_seconds * 1000);
        $this->setArguments($table)
            ->declare();
    }

    /**
     * 获取实例
     */
    public static function getInstance(string $name)
    {
        if (isset(static::$instances[$name]) && static::$instances[$name] instanceof static) {
            return static::$instances[$name];
        } else {
            static::$instances[$name] = new static($name);
            return static::$instances[$name];
        }
    }
}