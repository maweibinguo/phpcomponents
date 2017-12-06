<?php
namespace app\commands\redis;

use yii\console\Controller;
use Yii;

class RedisLockController extends Controller
{
    /**
     * 获取所有的文章列表
     */
    public function actionDeductGood()
    {
        $redis = Yii::$app->redis;

        $lock_key_name = 'second_kill';
        $redis->lock($key_name, 10);    
        $left_number = $redis->decr('goods_number');
        $redis->unlock($lock_key_name);
        echo 'the left number is : ' , $left_number,"\r\n";
    }
}
