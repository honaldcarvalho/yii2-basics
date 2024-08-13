<?php
namespace weebz\yii2basics\themes\weebz\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/weebz/yii2-basics/themes/weebz/web/dist/plugins/fontawesome-free';

    public $css = [
        'css/all.min.css'
    ];
}