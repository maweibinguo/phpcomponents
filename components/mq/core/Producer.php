<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:03
 */

namespace app\components\mq\core;

use app\components\mq\rabbitmq\BindingFactory;
use app\components\mq\rabbitmq\DeadLetterExchange;
use app\components\mq\rabbitmq\ExchangeFactory;
use app\components\mq\rabbitmq\QueueFactory;
use app\components\mq\rabbitmq\TaskFactory;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Wire\AMQPTable;

class Producer
{
    use \app\components\mq\rabbitmq\Channel;

    use \app\components\mq\rabbitmq\DeadLetterExchange;

    /**
     * 发布消息的属性
     */
    private $_publish_property = [
        'msg' => '',
        'exchange' => '',
        'routing_key' => '',
        'mandatory' => false,
        'immediate' => false,
        'ticket' => null
    ];

    /**
     * 设置消息体
     */
    public function setMsg(string $msg)
    {
        $this->_publish_property['msg'] = $msg;
        return $this;
    }

    /**
     * 设置交换机
     */
    public function setExchange(string $exchange)
    {
        $this->_publish_property['exchange'] = $exchange;
        return $this;
    }

    /**
     * 设置路由键
     */
    public function setRoutingKey(string $routing_key)
    {
        $this->_publish_property['routing_key'] = $routing_key;
        return $this;
    }

    /**
     * 设置mandatory
     */
    public function setMandatory(bool $mandatory)
    {
        $this->_publish_property['mandatory'] = $mandatory;
        return $this;
    }

    /**
     * 发布延迟数据
     */
    public function publishDelay(string $msg = '', string $queue = '', int $delay_seconds = 5)
    {
        try {
            /* 校验参数 */
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
            }
            if (empty($queue)) {
                throw new AMQPInvalidArgumentException('queue is empty');
            }
            if ($delay_seconds <= 0) {
                throw new AMQPInvalidArgumentException(sprintf("can't handle delay_seconds:%s", $delay_seconds));
            } else {
                $delay_seconds = $delay_seconds * 1000;
            }
            $delay_queue = sprintf("delay:%s:%s", $queue, $delay_seconds);

            /* 创建死信交换机 */
            ExchangeFactory::getInstance()->createDelayExchange();

            /* 创建业务交换机 */
            ExchangeFactory::getInstance()->createDefaultDirectExchange();

            /* 创建延迟队列*/
            QueueFactory::getInstance()->createDelayQueue($delay_queue, ExchangeFactory::DEFAULT_DIRECT_EXCHANGE, $queue, $delay_seconds);

            /* 创建业务队列 */
            QueueFactory::getInstance()->setQueue($queue)
                ->createQueue();

            /* 绑定延迟交换机和延迟队列 */
            BindingFactory::getInstance()->setExchange(DeadLetterExchange::$dead_letter_exchange_name)
                ->setQueue($delay_queue)
                ->setRoutingKey($delay_queue)
                ->bind();

            /* 绑定业务交换机和业务队列 */
            BindingFactory::getInstance()->setExchange(ExchangeFactory::DEFAULT_DIRECT_EXCHANGE)
                ->setQueue($queue)
                ->setRoutingKey($queue)
                ->bind();

            /* 创建消息 */
            $task_factory = new TaskFactory();
            $task = $task_factory->createTask($this->_publish_property['msg']);

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $this->setRoutingKey($delay_queue);
            $this->setExchange(DeadLetterExchange::$dead_letter_exchange_name);
            $result = $channel->basic_publish(
                $task,
                $this->_publish_property['exchange'],
                $this->_publish_property['routing_key'],
                $this->_publish_property['mandatory'],
                $this->_publish_property['immediate'],
                $this->_publish_property['ticket']
            );
            return true;
        } catch (\Exception $e) {
            var_dump($e->getLine(), $e->getFile(), $e->getMessage());
            die();
            return false;
        }
    }

    /**
     * 发布数据
     */
    public function publishDirect(string $msg = '', string $queue = '', string $exchange = ExchangeFactory::DEFAULT_DIRECT_EXCHANGE)
    {
        try {
            /* 校验参数 */
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
            }
            if (empty($queue)) {
                throw new AMQPInvalidArgumentException('queue is empty');
            } else {
                $this->setRoutingKey($queue);
            }

            $this->setExchange($exchange);

            /* 创建直连交换机 */
            ExchangeFactory::getInstance()->setExchangeName($exchange)
                ->setExchangeType(ExchangeFactory::DIRECT_TYPE)
                ->createExchange();

            /* 创建队列 */
            QueueFactory::getInstance()->setQueue($queue)
                ->createQueue();

            /* 绑定交换机和队列 */
            BindingFactory::getInstance()->setExchange($exchange)
                ->setQueue($queue)
                ->setRoutingKey($queue)
                ->bind();

            /* 创建消息 */
            $task_factory = new TaskFactory();
            $task = $task_factory->createTask($this->_publish_property['msg']);

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $result = $channel->basic_publish(
                $task,
                $this->_publish_property['exchange'],
                $this->_publish_property['routing_key'],
                $this->_publish_property['mandatory'],
                $this->_publish_property['immediate'],
                $this->_publish_property['ticket']
            );
            return true;
        } catch (\Exception $e) {
            var_dump($e->getLine(), $e->getFile(), $e->getMessage());
            die();
            return false;
        }
    }

    /**
     * 发布数据
     */
    public function publishTopic(string $msg, string $queue, string $binding_key, string $exchange = ExchangeFactory::DEFAULT_TOPIC_EXCHANGE)
    {
        try {
            /* 校验参数 */
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
            }
            if (empty($queue)) {
                throw new AMQPInvalidArgumentException('queue is empty');
            } else {
                $this->setRoutingKey($queue);
            }
            if(empty($binding_key)) {
                throw new AMQPInvalidArgumentException('binding_key is empty');
            }

            $this->setExchange($exchange);

            /* 创建广播交换机 */
            ExchangeFactory::getInstance()->setExchangeName($exchange)
                ->setExchangeType(ExchangeFactory::FANOUT_TYPE)
                ->createExchange();

            /* 创建队列 */
            QueueFactory::getInstance()->setQueue($queue)
                ->createQueue();

            /* 绑定交换机和队列 */
            BindingFactory::getInstance()->setExchange($exchange)
                ->setQueue($queue)
                ->setRoutingKey($binding_key)
                ->bind();

            /* 创建消息 */
            $task_factory = new TaskFactory();
            $task = $task_factory->createTask($this->_publish_property['msg']);

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $result = $channel->basic_publish(
                $task,
                $this->_publish_property['exchange'],
                $this->_publish_property['routing_key'],
                $this->_publish_property['mandatory'],
                $this->_publish_property['immediate'],
                $this->_publish_property['ticket']
            );
            return true;
        } catch (\Exception $e) {
            var_dump($e->getLine(), $e->getFile(), $e->getMessage());
            die();
            return false;
        }

    }

    /**
     * 发布数据
     */
    public function publishFanout(string $msg, string $queue, string $exchange = ExchangeFactory::DEFAULT_FANOUT_EXCHANGE)
    {
        try {
            /* 校验参数 */
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
            }
            if (empty($queue)) {
                throw new AMQPInvalidArgumentException('queue is empty');
            }

            $this->setExchange($exchange);

            /* 创建广播交换机 */
            ExchangeFactory::getInstance()->setExchangeName($exchange)
                ->setExchangeType(ExchangeFactory::FANOUT_TYPE)
                ->createExchange();

            /* 创建队列 */
            QueueFactory::getInstance()->setQueue($queue)
                ->createQueue();

            /* 绑定交换机和队列 */
            BindingFactory::getInstance()->setExchange($exchange)
                ->setQueue($queue)
                ->bind();

            /* 创建消息 */
            $task_factory = new TaskFactory();
            $task = $task_factory->createTask($this->_publish_property['msg']);

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $result = $channel->basic_publish(
                $task,
                $this->_publish_property['exchange'],
                $this->_publish_property['routing_key'],
                $this->_publish_property['mandatory'],
                $this->_publish_property['immediate'],
                $this->_publish_property['ticket']
            );
            return true;
        } catch (\Exception $e) {
            var_dump($e->getLine(), $e->getFile(), $e->getMessage());
            die();
            return false;
        }

    }
}