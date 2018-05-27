<?php
namespace app\commands\date;

use yii\console\Controller;
use Yii;
use Carbon\Carbon;

class DateController extends Controller
{
    /**
     * 获取所有的文章列表
     */
    public function actionTest()
    {
        $date_time_one = new \DateTime('2018-03-09 08:42:46', new \DateTimeZone('Asia/Tokyo'));
        //echo $date_time->format('Y-m-d H:i:s');//2018-03-09 08:42:46

        $date_time_two = new \DateTime('2018-03-09 08:42:46', new \DateTimeZone('UTC'));
        //echo $date_time->format('Y-m-d H:i:s');//2018-03-09 08:42:46

        $offset = $date_time_one->getOffset();
        var_dump($offset);die();
    }
}
