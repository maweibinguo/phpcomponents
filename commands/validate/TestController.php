<?php
namespace app\commands\validate;

use yii\console\Controller;
use Valitron\Validator;

class TestController extends Controller
{
    public function actionTest()
    {
       Validator::langDir('/mnt/hgfs/project/phpcomponents/vendor/vlucas/valitron/lang');
       Validator::lang('zh-cn');
       Validator::addRule('money', function($field, $value, array $params, array $fields){
           if(is_numeric($value) && $value > 0) {
            return true;
           } else {
            return false;
           }
//var_dump($field, $value, $params, $fields);die();
//string(4) "name"
//string(14) "Chester Tester"
//array(0) {
//}
//array(1) {
//  ["name"]=>
//  string(14) "Chester Tester"
//}
       }, '不是钱啊');

       $v = new Validator(array('name' => 'Chester Tester'));
       $v->rule('money', 'name');
       var_dump($v->validate(), $v->errors());die();
    }
}
