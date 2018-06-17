<?php
namespace app\commands\mq;

use PhpAmqpLib\Message\AMQPMessage;

class AckMqController extends MqController
{
    /**
     * 消息需要确认
     */  
    public function actionConfirmSend()
    {
        static::$channel->queue_declare('need_confirm', false, false, false, false);
        $msg = new AMQPMessage('You should confirm the message');
        static::$channel->basic_publish($msg, '', 'need_confirm');
        static::$channel->close();
        static::$connection->close();
        echo " Had Sent \r\n";
    }

    /**
     * 消息接收方
     */
    public function actionConfirmReceive()
    {
        static::$channel->queue_declare('need_confirm', false, false, false, false); 
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        //basic_consume is not blocked
        //第四个参数为false的代表数据需要确认
        static::$channel->basic_consume('need_confirm', '', false, false, false, false, [$this, 'confirmCallBack']);

        //callbacks is block
        while(count(static::$channel->callbacks)) {
            static::$channel->wait();
        }
    }

    /**
     * 消息的回调函数
     */
    public function confirmCallBack($message)
    {
        echo " [x] Received ", $message->body, "\n";

        $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], false);
        //$message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }

    /**
     * 消息确认
     *
     * 1、没有ack的话, 会由unacked 变为ready状态，再次丢入到队列执行
     * 2、如果该消息一直执行不成功的话，是否会阻塞后面的消息呢?答案是不会阻塞
     */

    /**
     * 消息确认应该注意的问题 @todo
     *
     * 1、首先需要明确一点，消息是否可以得到重复的执行，比如消费你消息的消费者，有调用接口扣款的地方，只是落库失败了
     * 此时是绝对不能执行消息的重复消费的
     *
     * 2、即使是能够重复消费也不应该无限制的去执行，应该做一个有上限的次数（否则，你的日志会马上写爆）
     *    为了能够限制消息的执行次数，我们需要解决下面的两个问题:
     *    a、如何确认唯一的一条消息为他计数(消息体中加唯一标志 - 发号器); 
     *    b、如何超过一定的次数就不再执行该消息了(拒绝);
     *
     * 3、忘记消息确认的话，会引起另外一个问题，造成大量的消息拥挤在内存中，队列会被撑爆
     */

}
