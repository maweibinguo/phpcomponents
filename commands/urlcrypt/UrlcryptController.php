<?php
namespace app\commands\urlcrypt;

use yii\console\Controller;
use Yii;
use Urlcrypt\Urlcrypt;

class UrlcryptController extends Controller
{
    /**
     * 获取所有的文章列表
     */
    public function actionTest()
    {
        ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
        //$encode = Urlcrypt::encode("aaron");//mfqx2664
        //$decode = Urlcrypt::decode("mfqx2664");

        Urlcrypt::$key = "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3";
        $encrypted = Urlcrypt::encrypt("aaron");
        // --> "q0dmt61xkjyylA5mp3gm23khd1kg6w7pzxvd3nzcgb047zx8y581"
        $decrypted = Urlcrypt::decrypt("q0dmt61xkjyylA5mp3gm23khd1kg6w7pzxvd3nzcgb047zx8y581");
var_dump($decrypted);die();
        // --> "aaron"
    }
}
