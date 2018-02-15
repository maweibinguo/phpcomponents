<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands\closure;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class LearnController extends Controller 
{
    public function actionTest()
    {
        $second = new Second();
        $class_name = get_class($second);
        $fun = \Closure::bind(function(){
            //$this->begin();//First::begin
            //$this->rollback();//First::rollback
            //$this->commit();//报错
        }, $second, $class_name);

        $fun();
    }   

    public function actionTestv2()
    {
        $second = new Second();
        $class_name = get_class(new First());
        $fun = \Closure::bind(function(){
            //$this->begin();//First::begin
            //$this->rollback();//First::rollback
            //$this->commit();//此时通过
            //$this->_hgetall();//此时报错
            //var_dump($this);//object(app\commands\closure\Second),此时的$this仍然是second，但是调用到了父类的私有方法，因为此时的作用域已经变为了First类的。于此同时虽然还是second对象，但是却调用不了second类的私有_hgetall()方法
        }, $second, $class_name);

        $fun();
    }   

    public function actionTestv3()
    {
        $second = new Second();
        $fun = \Closure::bind(function(){
            //var_dump($this);//object(app\commands\closure\Second)
            //这里我们并没有指定bind的作用域，那么就是默认的$second对象的作用域
        }, $second);

        $fun();
    }   
}