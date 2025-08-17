<?php
/* @var $this \yii\web\View */
/* @var $content string */

use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\models\Configuration;
use weebz\yii2basics\themes\adminlte3\assets\FontAwesomeAsset;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use weebz\yii2basics\themes\adminlte3\assets\WeebzAsset;
use yii\helpers\Html;

FontAwesomeAsset::register($this);
WeebzAsset::register($this);
PluginAsset::register($this)->add(['fontawesome', 'icheck-bootstrap','fancybox','jquery-ui','toastr','select2','sweetalert2']);
$params = Configuration::get();
$this->metaTags = '';
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');


$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist');

if(Yii::$app->user->identity === null){
    return (new ControllerCommon(0,0))->redirect(['site/login']); 
}
$theme = Yii::$app->user->identity->theme;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

    <!-- Required meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->title != '' ? $params->title . ' - ' . Html::encode($this->title) : $params->title  ?></title>
    <?php 
    $this->head(); 
    $script = <<< JS
        Fancybox.bind("[data-fancybox]");
        $(document).on('click', '[data-fancybox]', function () {
            if($.fancybox === undefined || $.fancybox === null) {
                console.log('Fancybox is not defined. Please ensure the Fancybox plugin is loaded.');
            } else {
                $.fancybox.showLoading = function () {
                    if ($('#custom-loading').length === 0) {
                        $('body').append('<div id="custom-loading" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;font-size:20px;">Carregando...</div>');
                    }
                };

                $.fancybox.hideLoading = function () {
                    $('#custom-loading').remove();
                };

                $.fancybox.showLoading();
            }
        });

        // Esconde após abrir o fancybox
        $(document).on('afterShow.fb', function () {
            $.fancybox.hideLoading();
        });

        // Também remove ao fechar (garantia extra)
        $(document).on('afterClose.fb', function () {
            $.fancybox.hideLoading();
        });
    JS;
    $this->registerJs($script);
    $this->registerCssFile('/css/custom.css', [
        'depends' => [\app\assets\AppAsset::class],
    ]);
    ?>

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
