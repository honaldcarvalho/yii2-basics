<?php

use weebz\yii2basics\widgets\MultiUpload;

echo MultiUpload::widget([
    'extensions'=>['jpeg','jpg','png'],
    'auto'=>true,
    'callback'=>'']); 
?>