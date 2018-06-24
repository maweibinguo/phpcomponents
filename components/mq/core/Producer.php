<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:03
 */

namespace app\components\mq\core;

use app\components\mq\rabbitmq\Binding;
use app\components\mq\rabbitmq\DeadLetter;
use app\components\mq\rabbitmq\Exchange;
use app\components\mq\rabbitmq\Queue;
use app\components\mq\rabbitmq\Task;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;

class Producer
{
    use \app\components\mq\rabbitmq\Channel;

    use \app\components\mq\rabbitmq\DeadLetter;

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

    public function publishDelay(string $msg = '', int $delay_seconds, string $queue = '', string $exchange = '', string $delay_exchange = '', string $routing_key = '')
    {
        try {
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
                $amqp_msg = (new Task($msg))->getAMQPMsg();
            }

            if($delay_seconds <= 0) {
                throw new AMQPInvalidArgumentException(sprintf('%s delay_seconds is wrong', $delay_seconds));
            }

            if (empty($queue)) {
                throw new AMQPInvalidArgumentException('queue is empty');
            } else {
                Queue::getInstance($queue)->declare();
                $delay_queue = DeadLetter::getDelayQueueName($queue, $delay_seconds);
                Queue::getInstance($delay_queue)->declareDelay($exchange, $routing_key, $delay_seconds);
            }

            if (empty($routing_key)) {
                $routing_key = $queue;
                $this->setRoutingKey($routing_key);
            } else {
                $this->setRoutingKey($routing_key);
            }

            if (empty($exchange)) {
                throw new AMQPInvalidArgumentException('exchange is empty');
            } else {
                Exchange::getInstance($exchange)->declare();
                //延迟交换机
                $delay_exchange = DeadLetter::getDelayExchangeName($exchange);
            }

                Exchange::getInstance(DeadLetter::$dead_letter_exchange_name)->declare();
                Queue::getInstance($queue)->declareDelay($exchange, $routing_key, $delay_seconds);
                Binding::getInstance($queue, $exchange, $routing_key)->bind();
                $this->setExchange(DeadLetter::$dead_letter_exchange_name);

            /* 声明业务交换机 */
            Exchange::getInstance($exchange)
                ->setExchangeType(Exchange::DIRECT_TYPE)
                ->declare();

            /* 绑定 */
            Binding::getInstance($queue, $exchange, $routing_key)->bind();

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $channel->basic_publish(
                $amqp_msg,
                $this->_publish_property['exchange'],
                $this->_publish_property['routing_key'],
                $this->_publish_property['mandatory'],
                $this->_publish_property['immediate'],
                $this->_publish_property['ticket']
            );
            echo "message had send\r\n";
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
    public function publishDirect(string $msg = '', string $queue = '', string $routing_key = '', int $delay_seconds = 0, string $exchange = Exchange::DEFAULT_DIRECT_EXCHANGE)
    {
        try {

            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
                $amqp_msg = (new Task($msg))->getAMQPMsg();
            }

            if (empty($queue)) {
                throw new AMQPInvalidArgumentException('queue is empty');
            } else {
                Queue::getInstance($queue)->declare();
            }

            if (empty($routing_key)) {
                $routing_key = $queue;
                $this->setRoutingKey($routing_key);
            } else {
                $this->setRoutingKey($routing_key);
            }

            if ($delay_seconds > 0) {
                Exchange::getInstance(DeadLetter::$dead_letter_exchange_name)->declare();
                Queue::getInstance($queue)->declareDelay($exchange, $routing_key, $delay_seconds);
                Binding::getInstance($queue, $exchange, $routing_key)->bind();
                $this->setExchange(DeadLetter::$dead_letter_exchange_name);
            } else {
                $this->setExchange($exchange);
            }

            /* 声明业务交换机 */
            Exchange::getInstance($exchange)
                ->setExchangeType(Exchange::DIRECT_TYPE)
                ->declare();

            /* 绑定 */
            Binding::getInstance($queue, $exchange, $routing_key)->bind();

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $channel->basic_publish(
                $amqp_msg,
                $this->_publish_property['exchange'],
                $this->_publish_property['routing_key'],
                $this->_publish_property['mandatory'],
                $this->_publish_property['immediate'],
                $this->_publish_property['ticket']
            );
            echo "message had send\r\n";
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
    public function publishTopic(string $msg, string $routing_key, string $exchange = Exchange::DEFAULT_TOPIC_EXCHANGE)
    {
        try {
            /* 校验参数 */
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
            }
            if (empty($routing_key)) {
                throw new AMQPInvalidArgumentException('routing_key is empty');
            } else {
                $this->setRoutingKey($routing_key);
            }

            $this->setExchange($exchange);

            /* 创建广播交换机 */
            Exchange::getInstance($exchange)
                ->setExchangeType(Exchange::FANOUT_TYPE)
                ->createExchange();

            /* 创建消息 */
            $amqp_msg = (new Task($msg))->getAMQPMsg();

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $channel->basic_publish(
                $amqp_msg,
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
    public function publishFanout(string $msg, string $exchange = Exchange::DEFAULT_FANOUT_EXCHANGE)
    {
        try {
            /* 校验参数 */
            if (empty($msg)) {
                throw new AMQPInvalidArgumentException('msg is empty');
            } else {
                $this->setMsg($msg);
            }

            $this->setExchange($exchange);

            /* 创建广播交换机 */
            Exchange::getInstance($exchange)
                ->setExchangeType(Exchange::FANOUT_TYPE)
                ->createExchange();

            /* 创建消息 */
            $amqp_msg = (new Task($msg))->getAMQPMsg();

            /* 发布消息 */
            /* @var $channel \PhpAmqpLib\Channel\AMQPChannel */
            $channel = static::getChannel();
            $channel->basic_publish(
                $amqp_msg,
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

============================== 分支=======================================
master_protocol_1027_20180514（已合并master）

============================= 定时任务 ===================================
php /var/www/protocol/xinhehui_protocol/cli/cli.php  command\\flx-supplement supply

============================= sql  ===================================
需要将xhh主站的xlb_join_user 表复制一份到签章系统
