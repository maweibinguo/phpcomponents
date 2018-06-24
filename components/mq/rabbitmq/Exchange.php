<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:05
 */

namespace app\components\mq\rabbitmq;

class Exchange
{
    use Channel;

    /**
     * 交换机类型
     */
    const DIRECT_TYPE = 'direct';
    const FANOUT_TYPE = 'fanout';
    const TOPIC_TYPE = 'topic';

    /**
     * 默认交换机名称
     */
    const DEFAULT_DIRECT_EXCHANGE = 'default_direct_exchange';
    const DEFAULT_FANOUT_EXCHANGE = 'default_fanout_exchange';
    const DEFAULT_TOPIC_EXCHANGE = 'default_topic_exchange';

    /**
     * 交换机属性
     */
    private $_exchange_property = [
        'exchange' => '',
        'type' => '',
        'passive' => false,
        'durable' => true,
        'auto_delete' => false,
        'internal' => false,
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    ];

    /**
     * 交换机工厂实例对象
     */
    public static $instances = [];

    /**
     * 构造函数
     */
    private function __construct(string $name)
    {
        $this->setExchangeName($name);
    }

    /**
     * 获取交换机类型
     */
    public function setExchangeType(string $exchange_type)
    {
        $this->_exchange_property['type'] = $exchange_type;
        return $this;
    }

    /**
     *
     * 设置交换机名称
     */
    public function setExchangeName(string $exchange_name)
    {
        $this->_exchange_property['exchange'] = $exchange_name;
        return $this;
    }

    /**
     * 设置是否是持久化
     */
    public function setDurable(bool $is_durable)
    {
        $this->_exchange_property['durable'] = $is_durable;
        return $this;
    }

    /**
     * 设置是否自动删除
     */
    public function setAutoDelete(bool $auto_delete)
    {
        $this->_exchange_property['auto_delete'] = $auto_delete;
        return $this;
    }

    /**
     * 获取交换机实例
     */
    public function declare()
    {
        /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
        $channel = static::getChannel();
        $channel->exchange_declare(
            $this->_exchange_property['exchange'],
            $this->_exchange_property['type'],
            $this->_exchange_property['passive'],
            $this->_exchange_property['durable'],
            $this->_exchange_property['auto_delete'],
            $this->_exchange_property['internal'],
            $this->_exchange_property['nowait'],
            $this->_exchange_property['arguments'],
            $this->_exchange_property['ticket']
        );
    }

    /**
     * 创建延迟交换机
     */
    public function createDelayExchange()
    {
        $this->setExchangeType(self::DIRECT_TYPE)
            ->setExchangeName(DeadLetter::$dead_letter_exchange_name)
            ->declare();
    }

    /**
     * 创建默认直连交换机
     */
    public function createDefaultDirectExchange()
    {
        $this->setExchangeType(self::DIRECT_TYPE)
            ->setExchangeName(self::DEFAULT_DIRECT_EXCHANGE)
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