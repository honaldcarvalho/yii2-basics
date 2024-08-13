<?php
/* @var $this \yii\web\View */
/* @var $content string */

use weebz\yii2basics\modules\common\themes\weebz\assets\FontAwesomeAsset;
use weebz\yii2basics\modules\common\themes\weebz\assets\WeebzAsset;
use yii\helpers\Html;

FontAwesomeAsset::register($this);
WeebzAsset::register($this);
$this->metaTags = '';
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');

$assetDir = Yii::$app->assetManager->getPublishedUrl('@app/modules/common/themes/weebz/web/dist');
$theme = Yii::$app->user->identity->theme;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

    <!-- Required meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); ?>
    
</head>
<body class="hold-transition sidebar-mini <?=$theme?>-mode">
<?php $this->beginBody() ?>

<div class="wrapper">
    <!-- Navbar -->
    <?= $this->render('navbar', ['assetDir' => $assetDir,'theme'=>$theme]) ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->render('sidebar', ['assetDir' => $assetDir,'theme'=>$theme]) ?>

    <!-- Content Wrapper. Contains page content -->
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir,'theme'=>$theme]) ?>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <?= $this->render('control-sidebar', [ 'assetDir' => $assetDir,'theme'=>$theme]) ?>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <?= $this->render('footer', [ 'assetDir' => $assetDir,'theme'=>$theme]) ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
