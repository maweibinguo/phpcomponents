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

class LogController extends Controller 
{
    
    public function actionLog()
    {
        // create a log channel
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('/tmp/log/', Logger::WARNING));

        // add records to the log
        $log->warning('Foo');
        $log->error('Bar'); 
    }
}
