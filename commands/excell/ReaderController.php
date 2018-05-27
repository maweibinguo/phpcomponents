<?php
namespace app\commands\excell;

use yii\console\Controller;
use Yii;

class ReaderController extends Controller
{
    /**
     * 读取excell
     */
    public function actionReadIt()
    {
        echo 'sdf';die();
        $file_name = __DIR__ . '/me.xlsx';
        $spread_sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_name);
        $sheet_data = $spread_sheet->getActiveSheet()->toArray(null, true, true, true);
        $number = 0;
        foreach($sheet_data as $item) {
            foreach($item as $key => $value) {
                if($value == 'from zero') {
                    $number++;
                }
            }
        }

        var_dump('from zero total number is '.$number);
    }
}
