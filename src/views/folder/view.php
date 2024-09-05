<?php

use weebz\yii2basics\controllers\AuthController;
use yii\helpers\Html;
use yii\widgets\DetailView;
use weebz\yii2basics\controllers\ AuthController;
use weebz\yii2basics\widgets\StorageUpload;
use weebz\yii2basics\widgets\StorageUploadMultiple;
use weebz\yii2basics\widgets\UploadFiles;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Folder */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Folders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$token =  AuthController::User()->access_token;

$script = <<< JS

    function removeFiles(e) {

        let el = $(e);
        let ids = $('#grid-files').yiiGridView('getSelectedRows');

        if (confirm('You really can remove this file(s)?')) {

            let keys = [];
            keys = keys.concat(ids);

            if(el.attr('id') !== 'remove-files'){
                keys.push(el.data('id'));
            }

            if(keys.length <= 0){
                alert("No files selected!");
                return false;
            }

            let old_class = el.children("i").attr('class');
            el.prop('disabled',true);
            object = el.children("i");
            object.removeClass(old_class);
            object.addClass('fas fa-sync fa-spin');

            $('#overlay-files').show();

            $.ajax({
                type: "POST",
                url: "/rest/storage/remove-files",
                data: {keys:keys},
                headers: {
                    'Authorization': `Bearer {$token}`
                },
            }).done(function(response) {     
                
                if(response.length > 0){
                    $.each(response, function (indexInArray, valueOfElement) { 
                        if(valueOfElement.success){
                            toastr.success(valueOfElement.message);
                        }else{
                            toastr.error(valueOfElement.message);
                        }
                    });
                }
                $.pjax.reload({container: "#list-files-grid", async: true});
                return false;
            }).fail(function (response) {
                toastr.error("Error on remove files!");
            }).always(function (response) {
                el.prop('disabled',false);
                object.removeClass('fas fa-sync fa-spin');
                object.attr('class',old_class);
            });

        }
        return false;
    }

    $(function(){

        $(document).on('pjax:send', function() {
            $('#overlay-files').show();
        })
        $(document).on('pjax:complete', function() {
            $('#overlay-files').hide()
        })

        Fancybox.bind("[data-fancybox]");

    });
JS;
$this::registerJs($script, $this::POS_END);

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
                                'class' => \weebz\yii2basics\components\gridview\ActionColumn::class,
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

    <div class="card" id="list-files">

        <div class="card-header">
            <h3 class="card-title"><?= Yii::t('app', 'List Files'); ?></h3>
        </div>

        <div class="card-body">
            <p>
                <?= Html::button(
                    '<i class="fas fa-trash mr-2"></i>' . \Yii::t('app', 'Remove Files'),
                    [
                        'onclick' => 'removeFiles(this)',
                        'class' => 'btn btn-danger',
                        'id' => 'remove-files',
                        "data-toggle" => "tooltip",
                        "data-placement" => "top",
                        "title" => \Yii::t('app', 'Remove Files')
                    ]
                ); ?>
            </p>
            <div class="row">
                <div class="col-md-12">

                    <div id='overlay-files' class='overlay' style='display:none;height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                        <div class='d-flex align-items-center'>
                            <strong> <?= Yii::t('app', 'Loading...') ?></strong>
                            <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                        </div>
                    </div>
                    <?php Pjax::begin(['id' => 'list-files-grid']) ?>
                    <?= yii\grid\GridView::widget([
                        'id' => 'grid-files',
                        'dataProvider' =>  $dataProvider,
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                                // you may configure additional properties here
                            ],
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
                                    if (!empty($data->urlThumb)) {
                                        $url = Yii::getAlias('@web') . $data->urlThumb;
                                        return Html::a(
                                            "<img class='brand-image img-circle elevation-3' width='50' src='{$url}' />",
                                            Yii::getAlias('@web') . $data->url,
                                            ['class' => 'btn btn-outline-secondary', "data-fancybox " => "", "title" => \Yii::t('app', 'View')]
                                        );
                                    } else {
                                        return Html::a(
                                            "<img class='brand-image img-circle elevation-3' width='50' src='/dummy/code.php?x=150x150/fff/000.jpg&text=NO PREVIEW' />",
                                            Yii::getAlias('@web') . $data->url,
                                            ['class' => 'btn btn-outline-secondary', "data-fancybox " => "", "title" => \Yii::t('app', 'View')]
                                        );
                                    }
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
                                'class' => \weebz\yii2basics\components\ActionColumn::class,
                                'headerOptions' => ['style' => 'width:10%'],
                                'template' => '{view}{remove}{delete}',
                                'path' => 'app',
                                'controller' => 'file',
                                'buttons' => [
                                    'remove' => function ($url, $model, $key) {
                                        return AuthController::verAuthorization('file', 'remove-file', $model) ?
                                            Html::a(
                                                '<i class="fas fa-unlink"></i>',
                                                yii\helpers\Url::to(['file/remove-file', 'id' => $model->id, 'folder' => $model->folder_id]),
                                                ['class' => 'btn btn-outline-secondary', "data-toggle" => "tooltip", "data-placement" => "top", "title" => \Yii::t('app', 'Remove from folder')]
                                            ) : '';
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return
                                            Html::button(
                                                '<i class="fas fa-trash"></i>',
                                                ['onclick' => 'removeFiles(this)', 'class' => 'btn btn-outline-secondary', "data-id" => $model->id, "data-toggle" => "tooltip", "data-placement" => "top", "title" => \Yii::t('app', 'Remove')]
                                            );
                                    },
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
</div>