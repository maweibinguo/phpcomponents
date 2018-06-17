<?php 
namespace app\commands\swoole;

use yii\console\Controller;

class ClientController extends Controller
{
    /**
     * client
     */
    public $client;

    /**
     * 初始化swoole
     */
    public function init()
    {
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $this->client->connect('127.0.0.1', 9501);
    }

    public function actionSend()
    {
        $this->client->send('hello server.');
        $response = $this->client->recv();
        echo $response . PHP_EOL;
        $this->client->close();
    }

    public function actionBuffer()
    {
        for($i=0; $i<=2; $i++) {
            $this->client->send("Just a test.\n");
        }
        $this->client->close();
    }
}
