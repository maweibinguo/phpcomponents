<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 17:51
 */

namespace app\components\mq\rabbitmq;

use Yii;
use PhpAmqpLib\Connection\AMQPStreamConnection;

trait Channel
{
    /**
     * 连接实例
     */
    private static $_connection;

    /**
     * 连接信道
     */
    private static $_channel;

    /**
     * 重试次数
     */
    private static $_retry_number = 0;

    /**
     * 获取信道
     */
    public static function getChannel()
    {
        if(!is_object(static::$_connection)) {
            while(static::$_retry_number < 3) {
                try{
                    $rabbitmq_config = \Yii::$app->params['rabbitMq'];
                    static::$_connection= new AMQPStreamConnection(  $rabbitmq_config['host'],
                        $rabbitmq_config['port'],
                        $rabbitmq_config['user'],
                        $rabbitmq_config['password']   );
                    if(is_object(static::$_connection)) break;
                } catch (\Exception $e) {
                    static::$_retry_number++;
                    usleep(static::$_retry_number * 10000);
                }
            }
        }

        if(!is_object(static::$_connection)) {
            throw new AMQPConnectionException('connect rabbitmq failed');
        } elseif(is_object(static::$_channel)) {
            return static::$_channel;
        } else {
            static::$_channel = static::$_connection->channel();
            return static::$_channel;
        }
    }
}