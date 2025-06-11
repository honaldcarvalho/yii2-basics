<?php
/* @var $content string */

use yii\bootstrap5\Breadcrumbs;
use weebz\yii2basics\widgets\Alert;

$script = <<< JS
    // Fancybox.bind("[data-fancybox]");
    // $(document).on('click', '[data-fancybox]', function () {
    //     // Mostra o carregando
    //     $.fancybox.showLoading = function () {
    //         if ($('#custom-loading').length === 0) {
    //             $('body').append('<div id="custom-loading" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;font-size:20px;">Carregando...</div>');
    //         }
    //     };

    //     // Esconde o carregando
    //     $.fancybox.hideLoading = function () {
    //         $('#custom-loading').remove();
    //     };

    //     $.fancybox.showLoading();
    // });

    // // Esconde após abrir o fancybox
    // $(document).on('afterShow.fb', function () {
    //     $.fancybox.hideLoading();
    // });

    // // Também remove ao fechar (garantia extra)
    // $(document).on('afterClose.fb', function () {
    //     $.fancybox.hideLoading();
    // });
JS;
$this->registerJs($script);
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?php
                        if (!is_null($this->title)) {
                            echo \yii\helpers\Html::encode($this->title);
                        } else {
                            echo \yii\helpers\Inflector::camelize($this->context->id).' / '.ucfirst(Yii::$app->controller->action->id);
                        }
                        ?>
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <?php
                    echo Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        'options' => [
                            'class' => 'breadcrumb float-sm-right'
                        ]
                    ]);
                    ?>
                </div><!-- /.col -->
            </div><!-- /.row -->
            <?= Alert::widget() ?>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <?= $content ?><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>