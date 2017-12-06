<?php
namespace app\commands\redis;

use yii\console\Controller;
use Yii;
use Geohash\Geohash;

class GeoController extends Controller
{
    /**
     * keyname
     */
    public $key_name = "tour";

    /**
     * 基于geohash值, 反解得出经纬度
     */
    public function actionDecodeGeoHash()
    {
        $result = Yii::$app->redis->geoAdd($this->key_name, '116.404412', '39.915046', 'TianAnMen',
                                                            '116.20003', '40.002428', 'XiangShan');
        $hash_list = Yii::$app->redis->geoHash($this->key_name, 'TianAnMen', 'XiangShan');
        foreach($hash_list as $hash) {
            $position_list = Geohash::decode($hash);
            var_dump($position_list);
        }
    }

    /**
     * 基于经纬度获取hash
     */
    public function actionEncodeHash()
    {
        $lat = '116.404412';
        $lon = '39.915046';
        $result = Geohash::encode($lat, $lon);
        var_dump($result);die();
    }
}
