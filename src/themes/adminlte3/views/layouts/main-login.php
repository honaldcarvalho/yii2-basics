<?php

/* @var $this \yii\web\View */
/* @var $content string */

use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\themes\adminlte3\assets\FontAwesomeAsset;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use weebz\yii2basics\themes\adminlte3\assets\WeebzAsset;
use weebz\yii2basics\widgets\Alert;

FontAwesomeAsset::register($this);
WeebzAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');
$this->registerCssFile('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
PluginAsset::register($this)->add(['fontawesome']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>WCMS | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= Yii::getAlias('@web') ?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= Yii::getAlias('@web') ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= Yii::getAlias('@web') ?>/favicon-16x16.png">
    <link rel="manifest" href="<?= Yii::getAlias('@web') ?>/site.webmanifest">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
    <link href="<?= Yii::getAlias('@web') ?>/css/site.css" rel="stylesheet">
</head>
<body class="hold-transition login-page">
<?= Alert::widget() ?>
<?php  $this->beginBody() ?>
<div class="login-box">
    <div class="login-logo">
        <a href="<?=Yii::$app->homeUrl?>">
        <img src="<?= ControllerCommon::$assetsDir; ?>/img/logo_weebz.png" alt="" class="w-50 brand-image img-circle elevation-3">
        </a>
    </div>
    <!-- /.login-logo -->

    <?= $content ?>
</div>
<!-- /.login-box -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>