<?php

namespace weebz\yii2basics\formatters;

use DateInterval;
use DateTime;
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

    public function asAbbreviatedRelativeTime($value)
    {
        // Get the relative time description
        $relativeTime = $this->asRelativeTime($value);

        // Abbreviate the time description
        $relativeTime = str_replace(['minute', 'minutes'], 'min', $relativeTime);
        $relativeTime = str_replace(['hour', 'hours'], 'hr', $relativeTime);
        $relativeTime = str_replace(['day', 'days'], 'd', $relativeTime);
        $relativeTime = str_replace(['week', 'weeks'], 'w', $relativeTime);
        $relativeTime = str_replace(['month', 'months'], 'mo', $relativeTime);
        $relativeTime = str_replace(['year', 'years'], 'yr', $relativeTime);

        return $relativeTime;
    }
    
    public function asAduration($value, $implodeString = ', ', $negativeSign = '-')
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof DateInterval) {
            $isNegative = $value->invert;
            $interval = $value;
        } elseif (is_numeric($value)) {
            $isNegative = $value < 0;
            $zeroDateTime = (new DateTime())->setTimestamp(0);
            $valueDateTime = (new DateTime())->setTimestamp(abs((int) $value));
            $interval = $valueDateTime->diff($zeroDateTime);
        } elseif (strncmp($value, 'P-', 2) === 0) {
            $interval = new DateInterval('P' . substr($value, 2));
            $isNegative = true;
        } else {
            $interval = new DateInterval($value);
            $isNegative = $interval->invert;
        }

        $parts = [];
        if ($interval->y > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 y} other{# y}}', ['delta' => $interval->y], $this->language);
        }
        if ($interval->m > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 m} other{# m}}', ['delta' => $interval->m], $this->language);
        }
        if ($interval->d > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 d} other{# d}}', ['delta' => $interval->d], $this->language);
        }
        if ($interval->h > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 h} other{# h}}', ['delta' => $interval->h], $this->language);
        }
        if ($interval->i > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 m} other{# m}}', ['delta' => $interval->i], $this->language);
        }
        if ($interval->s > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 a} other{# s}}', ['delta' => $interval->s], $this->language);
        }
        if ($interval->s === 0 && empty($parts)) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 a} other{# s}}', ['delta' => $interval->s], $this->language);
            $isNegative = false;
        }

        return empty($parts) ? $this->nullDisplay : (($isNegative ? $negativeSign : '') . implode($implodeString, $parts));
    }
}

