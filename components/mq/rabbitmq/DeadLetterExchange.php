<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/9
 * Time: 15:28
 */

namespace app\components\mq\rabbitmq;


use Codeception\Module\Queue;

trait DeadLetterExchange
{
    public static $dead_letter_exchange_name = 'dead_exchange';

    public static $default_routing_key = 'dead_queue';
}