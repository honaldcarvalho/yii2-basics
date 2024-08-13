<?php

use yii\bootstrap5\BootstrapAsset;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\File */
$this->registerJsFile(
    '@web/plugins/jquery-cropper/cropper.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerJsFile(
    '@web/plugins/jquery-cropper/jquery-cropper.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerCssFile("@web/plugins/jquery-cropper/cropper.min.css", [
    'depends' => [BootstrapAsset::class],
], 'cropper');

$this->title = Yii::t('app', 'Image Upload: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Files'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form_image', [
                        'model' => $model,
                        'accept'=> $accept
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>