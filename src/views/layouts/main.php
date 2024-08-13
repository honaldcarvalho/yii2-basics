<?php

/* @var $this \yii\web\View */
/* @var $content string */

use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\models\Params;
use yii\helpers\Html;

\hail812\adminlte3\assets\AdminLteAsset::register($this);

$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');


$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

$publishedRes = Yii::$app->assetManager->publish('@vendor/hail812/yii2-adminlte3/src/web/js');
$this->registerJsFile($publishedRes[1].'/control_sidebar.js', ['depends' => '\hail812\adminlte3\assets\AdminLteAsset']);
$control = new Controller(0, 0);
$params = Params::findOne(1);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= $this->title != '' ? $params->title . ' - ' . Html::encode($this->title) : $params->title  ?></title>
    <?php $this->head(); ?>

     <!--- FONTEWESOME -->
    <?= $this->registerCssFile("@web/css/themes.css"); ?>
    <?= $this->registerCssFile("@web/plugins/fontawesome-free/6/css/fontawesome.min.css"); ?>
    <?= $this->registerCssFile("@web/plugins/fontawesome-free/6/css/all.min.css"); ?>
    <?= $this->registerCssFile("@web/plugins/fontawesome-free/6/css/brands.min.css"); ?>

    <?= $this->registerCssFile("@web/plugins/icomoon/style.css"); ?>
    <?= $this->registerCssFile("@web/plugins/icomoon/customs.css"); ?>

    <!--- TOASTR -->
    <?= $this->registerCssFile("@web/plugins/toastr/toastr.min.css"); ?>
    <?= $this->registerJsFile('@web/plugins/toastr/toastr.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>

    <!--- FANCYBOX -->
    <?= $this->registerCssFile("@web/plugins/fancybox5/fancybox.css"); ?>
    <?= $this->registerJsFile('@web/plugins/fancybox5/fancybox.umd.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>

    <!--- SELECT2 --->
    <?= $this->registerCssFile("@web/plugins/select2/css/select2.min.css"); ?>
    <?= $this->registerJsFile('@web/plugins/select2/js/select2.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>

    <!--- DATATABLES -->
    <?= ''//this->registerCssFile("@web/plugins/datatables/datatables.min.css"); ?>
    <?= $this->registerCssFile("@web/plugins/datatables/dataTables.bootstrap5.min.css"); ?>
    <?= ''//$this->registerCssFile("@web/plugins/datatables/buttons.dataTables.min.css"); ?>
    <?= $this->registerCssFile("@web/plugins/datatables/buttons.bootstrap5.min.css"); ?>

    <?= $this->registerJsFile('@web/plugins/datatables/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>
    <?= $this->registerJsFile('@web/plugins/datatables/dataTables.buttons.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>
    <?= $this->registerJsFile('@web/plugins/datatables/buttons.bootstrap5.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>
    <?= $this->registerJsFile('@web/plugins/datatables/dataTables.bootstrap5.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>
    <?= $this->registerJsFile('@web/js/scripts.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>
    
    <!-- -->
</head>
<body class="hold-transition sidebar-mini dark-mode">
<?php $this->beginBody() ?>

<div class="wrapper">
    <!-- Navbar -->
    <?= $this->render('navbar', ['assetDir' => $assetDir,'control'=>$control,'params'=>$params]) ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->render('sidebar', ['assetDir' => $assetDir,'control'=>$control,'params'=>$params]) ?>

    <!-- Content Wrapper. Contains page content -->
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir,'control'=>$control,'params'=>$params]) ?>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <?= $this->render('control-sidebar',['control'=>$control,'params'=>$params]) ?>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <?= $this->render('footer',['control'=>$control,'params'=>$params]) ?>
</div>

<?php $this->endBody() ?>
    
</body>
</html>
<?php $this->endPage() ?>
