<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */




namespace app\controllers;

use yii\web\Controller;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Handler\SwiftMailerHandler;

class LogController extends Controller 
{
    
    public function actionLog()
    {
        $log = new Logger('apple');

        //为该channel添加回调处理数据
        $web_processor = new WebProcessor();
        $log->pushProcessor($web_processor);
        
        //创建streamhandler
        $log_name = date('Y-m-d') . '.log';
        $log_path = dirname(__DIR__) . '/runtime/logs/' . $log_name;
        $stream_handler = new StreamHandler($log_path, Logger::ALERT);

        //只为steamhandler添加回调处理数据
        $stream_handler->pushProcessor(function($record){
            $record['extra']['handler'] = 'stream_handler processor add';
            return $record;
        });

        //将streamhandler添加到channel对应stack中
        $log->pushHandler($stream_handler);

        //创建swiftMailerHandler
        $transport = (new \Swift_SmtpTransport(\Yii::$app->params['smtp_server'], \Yii::$app->params['port']))
                     ->setUsername(\Yii::$app->params['send_user'])
                     ->setPassword(\Yii::$app->params['password']);
        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message('Wonderful Subject'))->setFrom(['maweibinguo@126.com' => 'maweibin'])
                                                            ->setTo(['maweibinguo@163.com']);
        $swift_mailer_handler = new SwiftMailerHandler($mailer, $message);

        //此时不会交由streamHandler进行处理了
        $swift_mailer_handler->setBubble(false);

        //将fire_handler添加到channel对应stack中
        $log->pushHandler($swift_mailer_handler);

        //notice 函数只会记录 notice=250及其以下的记录，上面StreamHandler是alert=550，超过了该范围不会被记录
        //$log->notice("monolog", ["processer part"]);
        $log->emerg('monolog', ['emerg function add']);
        exit('success');
    }
}
