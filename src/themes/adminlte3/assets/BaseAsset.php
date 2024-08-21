<?php

namespace weebz\yii2basics\themes\adminlte3\assets;

use yii\web\AssetBundle;

class BaseAsset extends AssetBundle
{
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset'
    ];
}