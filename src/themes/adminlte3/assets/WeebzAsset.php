<?php
namespace weebz\yii2basics\themes\adminlte3\assets;

use yii\web\AssetBundle;

class WeebzAsset extends AssetBundle
{

    public $sourcePath = '@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist';

    public $css = [
        'css/custom.css',
        'plugins/bootstrap/css/bootstrap-grid.min.css',
        'css/adminlte.min.css',
    ];
    
    public $js = [
        'plugins/bootstrap/js/bootstrap.bundle.min.js',
        'js/adminlte.min.js',
        'js/utils.js',
        'js/t.js',
    ];

    public $depends = [
        'weebz\yii2basics\themes\adminlte3\assets\BaseAsset',
        'weebz\yii2basics\themes\adminlte3\assets\PluginAsset',
        '\yii\web\JqueryAsset'
    ];
}