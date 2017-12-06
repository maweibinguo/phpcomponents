<?php
namespace app\commands\redis;

use yii\console\Controller;
use Yii;

class LuaController extends Controller
{
    /**
     * 获取所有的文章列表
     */
    public function actionGetArticleList()
    {
        $redis = Yii::$app->redis;
        
        /**
         * KEYS 和 ARGV, 前者表示要操作的键名, 后者表示非键名参数
         */
        $command = <<<LUA
        local key_name = KEYS[1];
        local start = KEYS[2];
        local stop = KEYS[3];
        local article_common_key_list = redis.call('zrange', key_name, start, stop);
        local article_common_detail_list = {};
        local position = 0;
        for key, value in pairs(article_common_key_list) do
            position = position + 1;
            local article_detail = redis.call('hgetall', value);
            article_common_detail_list[position] = article_detail;
        end 
        return article_common_detail_list;
LUA;
        $result = $redis->eval($command, ['article:common:list', '0', '-1'], 3);
    }
}
