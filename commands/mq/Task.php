<?php
/**
 * Created by PhpStorm.
 * User: 002654
 * Date: 2018/5/27
 * Time: 16:13
 */

namespace app\commands\mq;


class Task
{
    public $task_id;

    public $body;

    public function __construct()
    {
        $this->task_id = uniqid('task');
        $this->setBody();
    }

    public function setBody()
    {
        $this->body = ['sdf', 'aaaa'];
    }

    public function test()
    {
        echo 'sdf';die();
    }

}