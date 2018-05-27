<?php
namespace app\commands\beanstalked;

use yii\console\Controller;
use Yii;
use Pheanstalk\Pheanstalk;

class BeanController extends Controller
{
    /**
     * beanstalked 常用命令测试
     */
    public function actionPut()
    {
        $beanstalkd = new Pheanstalk('127.0.0.1');
        $beanstalkd->useTube('zero')
                   ->put('my name is zero');
    }

    /**
     * reserver
     */
    public function actionReserve()
    {
        try{
            $beanstalkd = new Pheanstalk('127.0.0.1');
            $job = $beanstalkd->watch('zero')->reserve();
            $result = $beanstalkd->delete($job);
            echo 'sdf';die();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
