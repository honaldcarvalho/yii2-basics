<?php

/** @var yii\web\View $this */

use weebz\yii2basics\modules\common\models\Params;
use weebz\yii2basics\modules\common\controllers\ControllerCommon;

$params = Params::get();
$assetsDir = ControllerCommon::$assetsDir;
$logo_image = "<img src='{$assetsDir}/img/wcms_logo.png' width='150px' alt='{$params->title}' class='brand-image img-circle elevation-3' style='opacity: .8'>";
$this->title = '';

?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
         <p><?=$logo_image;?></p>
        <h4 class="display-5"><?= $params->title ?></h4>
    </div>

    <div class="body-content">
    </div>
</div>
