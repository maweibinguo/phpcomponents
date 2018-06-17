<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/10
 * Time: 17:34
 */

namespace app\commands\mq;

use yii\console\Controller;
use Yii;
use app\components\mq\core\Producer;

class TestController extends Controller
{
    public function actionTest()
    {
        $producer = new Producer();
        $producer->publishDirect('hello world', 'my_queue');
    }

    public function actionDelay()
    {
        $producer = new Producer();
        $producer->publishDelay('this is a delay message', 'business_queue', 60);
    }

    public function actionFanout()
    {
        $producer = new Producer();
        $producer->publishFanout('you had get a house', 'maweibin');
        $producer->publishFanout('you had get a house', 'guojinfeng');
    }

    public function actionTopic()
    {
        $producer = new Producer();
        //$producer->publishTopic('you had get a house', 'one.maweibin.one', '*.maweibin.*');
        $producer->publishTopic('you had get a house', 'two.maweibin.two', '*.maweibin.*');
    }
}