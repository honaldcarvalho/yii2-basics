<?php
namespace weebz\yii2basics\themes\weebz\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/weebz/yii2-basics/src/themes/weebz/web/dist/plugins/fontawesome-free';

    public $css = [
        'css/all.min.css'
    ];
}