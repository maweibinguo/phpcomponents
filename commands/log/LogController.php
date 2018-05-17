<?php
namespace app\commands\log;

use yii\console\Controller;
use Yii;
use Monolog\Logger;
use Monolog\Handler\MysqlDBHandler;

class LogController extends Controller
{
    /**
     * 获取所有的文章列表
     */
    public function actionTest()
    {
        $db = Yii::$app->db;
        $log = new Logger('zero'); 
        $handler = new MysqlDBHandler($db, Logger::WARNING);
        $log->pushHandler($handler);

        $log->warning('sdf');
    }
}
