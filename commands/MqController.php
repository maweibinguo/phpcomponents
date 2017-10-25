<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MqController extends Controller 
{
    /**
     * tpc 链接
     */
    public static $connection;

    /**
     * 信道链接
     */
    public static $channel;

    /**
     * 获取rabbitmq服务实例
     */
    public function init()
    {
        if(!is_object(static::$connection)) {
            $rabbitmq_config = \Yii::$app->params['rabbitMq'];
            static::$connection= new AMQPStreamConnection( $rabbitmq_config['host'], 
                                                    $rabbitmq_config['port'],
                                                    $rabbitmq_config['user'],
                                                    $rabbitmq_config['password']   );
            static::$channel = static::$connection->channel();
        }
    }

    /**
     * 例子1消息的回掉函数
     */
    public function msgCallBackOne($message)
    {
        echo " [x] Received ", $message->body, "\n";
    }

    /**
     * 列子2消息的回掉函数
     */
    public function msgCallBackTwo($message)
    {
        echo " [x] Received ", $message->body, "\n";
        $seconds = 0;
        while($seconds < 5) {
            $seconds++;
            echo $seconds,"\t";
            sleep(1);
        }

        //发送确认消息
        //没有ack的话, 会由unacked 变为ready状态，再次执行
        
        //如果该消息一直执行不成功的话，是否会阻塞后面的消息呢?答案是不会阻塞

        //其实每一个消息都不应该无限次的被执行，我们最好做成可配置的，最多执行多少次,超过该次数后就要丢弃该消息,同时保存执行失败的消息(最好发送短信或者邮件进行通知)
        //对于执行失败的数据我们就要手动补发了
        //============= 上面会引出两个问题 1、如何确认唯一的一条消息为他计数 2、如何超过一定的次数就不再执行该消息了

        //忘记消息确认的话，会引起另外一个问题，造成大量的消息拥挤在内存中
        if($message->body == 'test') {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    }

    /**
     ************************************************
     * direct nameless exchange
     ************************************************
     */
    /**
     * direct nameless exchange
     */
    public function actionOneSend()
    {
        static::$channel->queue_declare('hello', false, false, false, false);
        $msg = new AMQPMessage('Hello World!');
        static::$channel->basic_publish($msg, '', 'hello');
        static::$channel->close();
        static::$connection->close();
        echo " [x] Sent 'Hello World!'\n";
    }

     /**
      * 此时如果有多个消费者的话，那么消息会在多个消费者中间轮流发放
      */
    public function actionOneGet()
    {
        static::$channel->queue_declare('hello', false, false, false, false); 
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        //basic_consume is not blocked
        static::$channel->basic_consume('hello', '', false, true, false, false, [$this, 'msgCallBackOne']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }

        //never excuted
        echo '++++++++++++++++++++';
    }

    /**
     ************************************************
     * direct nameless exchange -- confirm messsage
     *
     * 由于对同一个队列声明的时候，不可以使用不同的参数，这里重新穿件confirm_hello队列
     ************************************************
     */
    public function actionTwoSend()
    {
        static::$channel->queue_declare('confirm_hello', false, false, false, false);
        $msg = new AMQPMessage('test');
        //$msg = new AMQPMessage('Hello World!');
        static::$channel->basic_publish($msg, '', 'confirm_hello');
        static::$channel->close();
        static::$connection->close();
        echo " [x] Sent 'Hello World!'\n";
    }

     /**
      * 正常情况下，走到回调函数的时候，将会从队列中删除该消息，此时不可保证业务完整执行完了
      * 我们需要基于业务，手动确认该消息
      * basic_consume 第四个参数为是否自动确认，true自动确认、false手动确认
      */
    public function actionTwoGet()
    {
        static::$channel->queue_declare('confirm_hello', false, false, false, false); 
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        //basic_consume is not blocked
        static::$channel->basic_consume('confirm_hello', '', false, false, false, false, [$this, 'msgCallBackTwo']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }

        //never excuted
        echo '++++++++++++++++++++';
    }


}
