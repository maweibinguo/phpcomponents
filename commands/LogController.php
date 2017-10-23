<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\WebProcessor;

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
        $log_path = dirname(__DIR__) . '/runtime/log/' . $log_name;
        $streamHandler = new StreamHandler($log_path, Logger::NOTICE);

        //只为steamhandler添加回调处理数据
        $streamHandler->pushProcessor(function($record){
            $record['extra'] = [ 'source_from' => 'stream_handler' ];
            return $record;
        });

        //将streamhandler添加到channel对应stack中
        $log->pushHandler($streamHandler);

        //如果没有手动添加handler的话，StreamHandler('php://stderr', static::DEBUG)
        $log->alert("monolog", ["processer part"]);
    }
}
