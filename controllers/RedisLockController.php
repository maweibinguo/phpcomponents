<?php
namespace app\controllers;

use yii\web\Controller;
use Yii;

class RedisLockController extends Controller
{
    /**
     * 获取所有的文章列表
     */
    public function actionDeductGood()
    {
        $redis = Yii::$app->redis;

        $command = <<<LUA
        local key_name = KEYS[1]
        local left_number = redis.call('get', key_name)
        local number
        if( tonumber(left_number) <= 0 ) 
        then
            return left_number
        else
            number = redis.call('decr', 'goods_number')
            return number
        end
LUA;
        $number = $redis->eval($command, $args = ['goods_number'], $num_keys = 1 );
        var_dump($number);die();
    }

    /**
     * 经过测试发现即使使用了redislock 也没有解决超发的问题，个人认为原因是竞态条件;
     *
     * 竞态条件：redis后面的命令对前面执行的命令有依赖, 当出现多个请求时，就会产生一些意外的情况
     * 这是由于redis命令本身没有原子性，我们没有办法控制redis执行命令的先后顺序
     * @redis 究竟是怎样执行命令的呢？
     */
    //public function actionDeductGood()
    //{
    //    $redis = Yii::$app->redis;

    //    $lock_key_name = 'second_kill';
    //    $lock_result = $redis->lock($lock_key_name, 5, 60);
    //    if($lock_result !== true) {
    //        return false;
    //    }
    //    $redis->incr('lock_num');
    //    $now_number = (int)$redis->get('goods_number');
    //    $redis->set('now_number', $now_number);
    //    sleep(1);

    //    if($now_number <= 0) {
    //        return false;
    //    }
    //    $left_number = $redis->decr('goods_number');

    //    $redis->unlock($lock_key_name);
    //}
}
