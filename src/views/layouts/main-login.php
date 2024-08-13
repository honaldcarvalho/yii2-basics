<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;

\hail812\adminlte3\assets\AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');
$this->registerCssFile('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
\hail812\adminlte3\assets\PluginAsset::register($this)->add(['fontawesome', 'icheck-bootstrap']);
$params = \weebz\yii2basics\modules\common\models\Params::findOne(1);

if(!empty($params->file_id)){
    $url = Yii::getAlias('@web').$params->file->urlThumb; 
    $logo_image = "<img alt='{$params->title}' width='150px' class='brand-image img-circle elevation-3' src='{$url}' style='opacity: .8' />";
}else{
    $logo_image = "<img src='/images/logo_weebz.png' width='150px' alt='{$params->title}' class='brand-image img-circle elevation-3' style='opacity: .8'>";
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$params->title;?> | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head(); ?>
    
    <!-- THEME -->
    <?= $this->registerCssFile("@web/css/themes.css"); ?>

</head>
<body class="hold-transition login-page">
<?php  $this->beginBody() ?>
<?= Alert::widget() ?>
<div class="login-box">
    <div class="login-logo">
        <?=$logo_image;?><br>
        <b><?=$params->title;?></b> | LOGIN</a>
    </div>
    <!-- /.login-logo -->

    <?= $content ?>
</div>
<!-- /.login-box -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>