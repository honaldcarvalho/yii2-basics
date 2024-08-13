<?php

use app\widgets\MultiUpload;

echo MultiUpload::widget([
    'extensions'=>['jpeg','jpg','png'],
    'auto'=>true,
    'callback'=>'']); 
?>