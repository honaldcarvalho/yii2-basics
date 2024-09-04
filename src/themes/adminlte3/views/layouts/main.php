<?php
/* @var $this \yii\web\View */
/* @var $content string */

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\models\Params;
use weebz\yii2basics\themes\adminlte3\assets\FontAwesomeAsset;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use weebz\yii2basics\themes\adminlte3\assets\WeebzAsset;
use yii\helpers\Html;

FontAwesomeAsset::register($this);
WeebzAsset::register($this);
PluginAsset::register($this)->add(['fontawesome', 'icheck-bootstrap','fancybox','jquery-ui','toastr','select2']);
$params = Params::get();
$this->metaTags = '';
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist');
$theme = 'light';
if(!AuthController::isGuest()){
    $theme = Yii::$app->user->identity->theme;
}else{
    $this->goHome();
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

    <!-- Required meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->title != '' ? $params->title . ' - ' . Html::encode($this->title) : $params->title  ?></title>
    <?php $this->head(); ?>
    
</head>
<body class="hold-transition sidebar-mini <?=$theme?>-mode">
<?php $this->beginBody() ?>

<div class="wrapper">
    <!-- Navbar -->
    <?= $this->render('navbar', ['assetDir' => $assetDir,'theme'=>$theme,'params'=>$params]) ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->render('sidebar', ['assetDir' => $assetDir,'theme'=>$theme,'params'=>$params]) ?>

    <!-- Content Wrapper. Contains page content -->
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir,'theme'=>$theme,'params'=>$params]) ?>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <?= $this->render('control-sidebar', [ 'assetDir' => $assetDir,'theme'=>$theme,'params'=>$params]) ?>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <?= $this->render('footer', [ 'assetDir' => $assetDir,'theme'=>$theme,'params'=>$params]) ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
