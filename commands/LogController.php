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
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Processor\WebProcessor;

class LogController extends Controller 
{
    
    public function actionLog()
    {
        //$transport = (new \Swift_SmtpTransport('smtp.163.com', 25))->setUsername('maweibinzero@163.com')->setPassword('123qweasd');
        //$mailer = new \Swift_Mailer($transport);

        /*$message = (new \Swift_Message('Mail Comes From Swift'))->setFrom(['maweibinzero@163.com' => 'maweibin'])
                                                                ->setTo(['maweibinguo@163.com'])
                                                                ->setBody("Here Is The Test Comes From Swift");*/
        // create a log channel
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('/tmp/log/zero.log', Logger::WARNING));
        //$mailLogHandler = new SwiftMailerHandler($mailer, $message, Logger::ERROR);
        //$log->pushHandler($mailLogHandler);

        // add records to the log
        $webProcessor = new WebProcessor();
        $log->pushProcessor($webProcessor);
        $log->warning('Foo', ['ma', 'wei', 'bin']);
        $log->error('Bar'); 
    }
}
