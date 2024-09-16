<?php

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\widgets\ListFiles;
use yii\helpers\Html;
use yii\widgets\DetailView;
use weebz\yii2basics\widgets\StorageUploadMultiple;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Folder */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Folders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= weebz\yii2basics\widgets\DefaultButtons::widget(['controller' => 'Folder', 'model' => $model]) ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'name',
                            'description',
                            'external:boolean',
                            'created_at:datetime',
                            'updated_at:datetime',
                            'status:boolean',
                        ],
                    ]) ?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
    </div>
    <!--.card-->

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Yii::t('app', 'Add File'); ?></h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= StorageUploadMultiple::widget([
                        'folder_id' => $model->id,
                        'group_id' => 1,
                        'grid_reload'=>1,
                        'grid_reload_id'=>'#list-files-grid'
                    ]); ?>
                </div>
            </div>
            <!--.row-->
        </div>
        <!--.card-->
    </div>
    <!--.card-->

    <div class="card" id="list-folders">

        <div class="card-header">
            <h3 class="card-title"><?= Yii::t('app', 'List Folders'); ?></h3>
        </div>

        <div class="card-body">

            <div id='overlay-folders' class='overlay' style='display:none;height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                <div class='d-flex align-items-center'>
                    <strong> <?= Yii::t('app', 'Loading...') ?></strong>
                    <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">

                    <?php Pjax::begin(['id' => 'list-folders-grid']) ?>
                    <?= yii\grid\GridView::widget([
                        'id' => 'grid-folders',
                        'dataProvider' =>  $dataProviderFolders,
                        'columns' => [
                            [
                                'headerOptions' => ['style' => 'width:4%'],
                                'attribute' => 'folder_id',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    if ($data->folder_id != null)
                                        return Html::a($data->folder->name, Url::toRoute([Yii::getAlias('@web/folder/view'), 'id' => $data->folder_id]));
                                }
                            ],
                            [
                                'headerOptions' => ['style' => 'width:20%'],
                                'attribute' => 'description',
                                'label' => Yii::t('app', 'Description'),
                            ],
                            //'path',
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'header' => 'Preview',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        "<img class='brand-image img-circle elevation-3' width='50' src='/dummy/code.php?x=150x150/fff/000.jpg&text=FOLDER' />",
                                        "/folder/$data->id",
                                        ['class' => 'btn btn-outline-secondary', "data-fancybox " => "", "title" => \Yii::t('app', 'View')]
                                    );
                                }
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute' => 'extension',
                                'label' => Yii::t('app', 'Extension'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute' => 'size',
                                'format' => 'bytes',
                                'label' => Yii::t('app', 'Size'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute' => 'duration',
                                'format' => 'time',
                                'label' => Yii::t('app', 'Duration'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:25%'],
                                'attribute' => 'created_at',
                                'format' => 'date',
                                'label' => Yii::t('app', 'Created At'),
                            ],
                            [
                                'class'=>weebz\yii2basics\components\gridview\ActionColumn::class,
                                'headerOptions' => ['style' => 'width:10%'],
                                'template' => '{remove}{delete}',
                                'path' => 'app',
                                'controller' => 'folder',
                                'buttons' => [
                                    'remove' => function ($url, $model, $key) {
                                        return AuthController::verAuthorization('file', 'remove-file', $model) ?
                                            Html::a(
                                                '<i class="fas fa-unlink"></i>',
                                                yii\helpers\Url::to(['file/remove-file', 'id' => $model->id, 'folder' => $model->folder_id]),
                                                ['class' => 'btn btn-outline-secondary', "data-toggle" => "tooltip", "data-placement" => "top", "title" => \Yii::t('app', 'Remove from folder')]
                                            ) : '';
                                    }
                                ]
                            ],
                        ],
                    ]); ?>

                    <?php Pjax::end() ?>

                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>

    </div>
    <!--.card-->

    
    <?= ListFiles::widget([
        'dataProvider' => $dataProvider,
    ]); ?>
</div>