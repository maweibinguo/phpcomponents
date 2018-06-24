<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/9
 * Time: 15:28
 */

namespace app\components\mq\rabbitmq;


use Codeception\Module\Queue;

trait DeadLetter
{
    public static $delay_exchange_name_tpl = 'delay_exchange_%s';

    public static $delay_queue_name_tpl = 'delay_queue_%s_%s';

    /**
     * @param string $exchange_name
     */
    public static function getDelayExchangeName(string $exchange)
    {
        $exchange_name = sprintf(static::$delay_exchange_name_tpl, $exchange);
        return $exchange_name;
    }

    public static function getDelayQueueName(string $queue, int $delay_seconds)
    {
        $queue_name = sprintf(static::$delay_queue_name_tpl, $queue, $delay_seconds);
        return $queue_name;
    }
}