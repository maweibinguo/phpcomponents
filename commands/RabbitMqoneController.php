<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqoneController extends Controller 
{
    /**
     * rabbimq服务实例
     */
    public static $mq;

    /**
     * 获取rabbitmq服务实例
     */
    public static function getRabbitMqInstance()
    {
        if(is_object(static::$mq)) {
            return static::$mq;
        } else {
            $rabbitmq_config = \Yii::$app->params['rabbitMq'];

            static::$mq = new AMQPStreamConnection( $rabbitmq_config['host'], 
                                                    $rabbitmq_config['port'],
                                                    $rabbitmq_config['user'],
                                                    $rabbitmq_config['password']   );
            var_dump(static::$mq);die();
            return static::$mq;
        }
    }

    /**
     * 在sso站点开始注册用户
     */
    public function actionRegisterSso()
    {
        var_dump(static::getRabbitMqInstance());die();
        var_dump(\Yii::$app->db);die();
    }

    /**
     * 在A站点注册用户
     */
    public function actionRegisterWeba()
    {
    
    }

    /**
     * 在B站点进行注册
     */
    public function actionRegisterWebb()
    {
    
    }

    /**
     * 
     */
    public function actionGetFace()
    {
        $content = file_get_contents(__DIR__ . '/index.html');
        $reg = '#data-src="([\w/\.]+)"#u';
        preg_match_all($reg, $content, $matches_list);
        $url_list = $matches_list[1];
        foreach($url_list as $url) {
            $target_url = "https://www.webpagefx.com/tools/emoji-cheat-sheet/" . $url;
            $dir_list = explode('/',$url);
            $file_name = end($dir_list);
            unset($dir_list[count($dir_list)-1]);
            $dir = __DIR__ ;
            foreach($dir_list as $dir_name) {
                $dir = $dir . '/' . $dir_name;
                if(file_exists($dir)) {
                    mkdir($dir);
                }
            }
            $target_file = $dir . '/' . $file_name;
            echo $target_url,"\r\n";
            file_put_contents('/tmp/urllist.txt', $target_url."\r\n", FILE_APPEND);
        }
    }
    
    public function actionTest()
    {
    
    }
}
