<?php
namespace app\commands\stdcache;

use yii\console\Controller;
use Yii;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class Psr6Controller extends Controller
{
    /**
     * 
     */
    public function actionCache()
    {
        $redis = Yii::$app->get('redis');
        $cache_dapter = new RedisAdapter($redis);
        $cach_item = $cache_dapter->getItem("fuck");
        $cach_item->set('you');
        $second_item = $cache_dapter->getItem('second');
        $second_item->set('two');
        $cache_dapter->save($cach_item);

        $stockes = $cache_dapter->getItems(['fuck', 'second']);
        foreach($stockes as $value) {
        }
        die();
    }
}
