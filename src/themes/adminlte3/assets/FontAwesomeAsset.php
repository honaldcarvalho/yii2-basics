<?php
namespace weebz\yii2basics\themes\adminlte3\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist/plugins/fontawesome-free';

    public $css = [
        'css/all.min.css'
    ];
}