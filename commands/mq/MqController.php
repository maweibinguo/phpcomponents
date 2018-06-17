<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands\mq;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MqController extends Controller 
{
    /**
     * 链接
     */
    public static $connection;

    /**
     * 信道链接
     */
    public static $channel;

    /**
     * 重试链接次数
     */
    public static $max_retry_times = 3;

    /**
     * 获取rabbitmq服务实例
     */
    public function init()
    {
        if(!is_object(static::$connection)) {
            try{
                $rabbitmq_config = \Yii::$app->params['rabbitMq'];
                static::$connection= new AMQPStreamConnection( $rabbitmq_config['host'],
                    $rabbitmq_config['port'],
                    $rabbitmq_config['user'],
                    $rabbitmq_config['password']   );
                static::$channel = static::$connection->channel();
            } catch (\Exception $e) {
                var_dump($e->getMessage());die();
            }
        }
    }

}
