<?php

namespace weebz\yii2basics\formatters;

use Yii;
use yii\web\View;

class Custom extends \yii\i18n\Formatter
{  

    public function asTranslate($value,$category= '*') { 
        return Yii::t($category,$value);

    }

    public function asPassword($password,$id = '') { 
        if($password === null){
            return null;
        }
        $random = rand(10000,99999);
        $script = <<< JS

            $("#{$random}toggle-password_$id").click(function() {
  
                    $("#{$random}password-text_$id").toggle();
                    $("#{$random}password-fake_$id").toggle();
                    showing = true;;
    
                $("{$random}field-icon").toggleClass("fa-eye fa-eye-slash");
            });
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        return '<button class="btn btn-default" id="'.$random.'toggle-password_'.$id.'" ><i class="fa fa-fw fa-eye field-icon"></i></button><span id="'.$random.'password-fake_'.$id.'">*****</span> <span id="'.$random.'password-text_'.$id.'" style="display:none;">'.$password.'</span>';

    }
    
    public function asBytes($bytes, $precision = 2) { 
        if($bytes === null){
            return null;
        }
        $base = log($bytes, 1024);
        $suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');   
    
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }
    
    public function asBits($fileSizeInBytes){
        if($fileSizeInBytes === null){
            return null;
        }
        $i = -1;
        $s = 0;
        if($fileSizeInBytes === 0 || $fileSizeInBytes === '0'){
            return '0 Kbps';
        }
        $byteUnits = [
          " Kbps",
          " Mbps",
          " Gbps",
          " Tbps",
          " Pbps",
          " Ebps",
          " Zbps",
          " Ybps"
        ];

        do {
          if(is_infinite($fileSizeInBytes / 1000)){
              return "INFINITO";
              break;
          }
          $fileSizeInBytes = $fileSizeInBytes / 1000;
          $i++;
          $s++;
        } while ($fileSizeInBytes > 1000);

        return number_format(max($fileSizeInBytes, 0.1), 2, '.', '') . $byteUnits[$i];
    }
}

