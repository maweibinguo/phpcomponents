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
     * tpc 链接
     */
    public static $connection;

    /**
     * 信道链接
     */
    public static $channel;

    /**
     * 获取rabbitmq服务实例
     */
    public function init()
    {
        if(!is_object(static::$connection)) {
            $rabbitmq_config = \Yii::$app->params['rabbitMq'];
            static::$connection= new AMQPStreamConnection( $rabbitmq_config['host'], 
                                                    $rabbitmq_config['port'],
                                                    $rabbitmq_config['user'],
                                                    $rabbitmq_config['password']   );
            static::$channel = static::$connection->channel();
        }
    }

}
