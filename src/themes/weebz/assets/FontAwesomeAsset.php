<?php
namespace weebz\yii2basics\modules\common\themes\weebz\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/common/themes/weebz/web/dist/plugins/fontawesome-free';

    public $css = [
        'css/all.min.css'
    ];
}