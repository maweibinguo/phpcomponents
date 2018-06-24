<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:15
 */

namespace app\components\mq\rabbitmq;

use PhpAmqpLib\Message\AMQPMessage;

class Task
{
    use Channel;
    use DeadLetter;

    private $msg = '';

    private $_task_property = [
        'content_type' => 'text/plain',
        'priority' => 'octet',
    ];

    /**
     * 构造函数
     */
    public function __construct(string $msg)
    {
        $this->msg = trim($msg);
    }

    /**
     * 获取消息体
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * 设置消息体格式
     */
    public function setContentType(string $content_type)
    {
        $this->_task_property['content_type'] = $content_type;
        return $this;
    }

    /**
     * 设置队列优先级 @todo
     */
    public function setPriority(string $priority)
    {
        $this->_task_property['priority'] = $priority;
        return $this;
    }

    /**
     * 是指rpc请求标志
     */
    protected function setCorrelationId(string $unique_id)
    {
        $this->_task_property['correlation_id'] = $unique_id;
        return $this;
    }

    /**
     * 设置rpc响应队列
     */
    public function setReplyTo(string $queue_name)
    {
        $this->_task_property['reply_to'] = $queue_name;
        return $this;
    }

    /**
     * 设置过期时间
     */
    public function setExpiration(int $seconds)
    {
        $seconds = $seconds * 1000;
        $this->_task_property['expiration'] = $seconds;
        return $this;
    }

    /**
     * 创建消息
     */
    public function getAMQPMsg()
    {
        $amqp_message = new AMQPMessage($this->msg, $this->_task_property);
        return $amqp_message;
    }
}