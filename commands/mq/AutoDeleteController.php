<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/1
 * Time: 11:03
 */

namespace app\commands\mq;


class AutoDeleteController extends MqController
{
    public $exchange_name = 'auto_delete_exchange';

    public $queue_name = 'auto_delete_queue';

    /**
     * 创建交换机
     */
    /**
     * 通过实验证明交换机设置成为自动删除属性后，发生删除动作是解绑触发的
     * 当最后一个队列同交换机解绑后，交换机就会被自动删除掉
     */
    public function actionCreateExchange()
    {
        static::$channel->exchange_declare(
            $exchange = $this->exchange_name,
            $type = 'direct',
            $passive = false,
            $durable = false,
            $auto_delete = true,
            $internal = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        static::$channel->queue_declare(
            $queue_name = $this->queue_name,
            $passive = false,
            $is_durable = true,
            $exclusive = false,
            $auto_delete = false,
            $no_wait = false,
            $arguments = null,
            $ticket = null
        );

        static::$channel->queue_bind(
            $queue = $this->queue_name,
            $exchange = $this->exchange_name,
            $routing_key = 'fuck',
            $nowait = false,
            $arguments = null,
            $ticket = null
        );
    }

    /**
     * 接触绑定
     */
    public function actionUnBind()
    {
        static::$channel->queue_unbind(
                        $queue = $this->queue_name,
                        $exchange = $this->exchange_name,
                        $routing_key = 'fuck',
                        $arguments = null,
                        $ticket = null);
    }
}