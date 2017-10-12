<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;

class JwtController extends Controller 
{
    /**
     * 错误认知1、
     * jwt的encode似乎只是做签名用的，主要是为了防止数据被篡改，
     * 但是并不能保证信息不被泄露，只要对head和payload进行base64_decode就可以获取相关的信息
     */

}
