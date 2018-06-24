<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/6/1
 * Time: 11:03
 */

namespace app\commands\mq\demo;

use yii\console\Controller;
use app\components\mq\core\Producer;

class PublishController extends Controller
{
    /**
     * 发送消息
     */
    public function actionPublishDirect()
    {
        $producer = new Producer();
        $producer->publishDirect(sprintf('message come from %s', __METHOD__), 'goods_order', '', 10);
    }
}
