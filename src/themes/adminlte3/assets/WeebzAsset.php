<?php
namespace weebz\yii2basics\themes\adminlte3\assets;

use yii\web\AssetBundle;

class WeebzAsset extends AssetBundle
{

    public $sourcePath = '@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist';

    public $css = [
        'css/dark.css',
        'plugins/bootstrap/css/bootstrap-grid.min.css',
        'plugins/toastr/toastr.min.css',
        'css/adminlte.min.css',
        'plugins/select2/css/select2.min.css'
    ];
    
    public $js = [
        'plugins/bootstrap/js/bootstrap.bundle.min.js',
        'plugins/toastr/toastr.min.js',
        'js/adminlte.min.js',
        'plugins/select2/js/select2.full.min.js',
        'plugins/multiselect/multiselect.min.js'
    ];

    public $depends = [
        'weebz\yii2basics\themes\adminlte3\assets\BaseAsset',
        'weebz\yii2basics\themes\adminlte3\assets\PluginAsset',
        '\yii\web\JqueryAsset'
    ];
}