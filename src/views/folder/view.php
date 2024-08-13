<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use weebz\yii2basics\controllers\ControllerCommon;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Folder */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Folders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$script = <<< JS
    $(function(){

        $(document).on('pjax:send', function() {
            $('#overlay-files').show();
        })
        $(document).on('pjax:complete', function() {
            $('#overlay-files').hide()
        })

        Fancybox.bind("[data-fancybox]");

        $('#delete-files').click(function(e){

            var items = $('.file-item:checked');
            if(items.length > 0){     
                var form = document.createElement('form');
                form.setAttribute('action','/file/delete-files?folder=$model->id');
                form.setAttribute('method','post');
                form.setAttribute('id','form-move');
                document.body.appendChild(form);
                
                $('.file-item:checked').each(function(i){
                    $(this).clone().appendTo('#form-move');
                });
                form.submit(); 
            }
            return false;
        });

    });
JS;
$this::registerJs($script,$this::POS_END);

$delete_files_button[] = 
[
    'controller'=>'file',
    'action'=>'delete-files',
    'icon'=>'<i class="fas fa-trash"></i>',
    'text'=>'Delete File(s)',
    'link'=>'javascript:;',
    'options'=>                    [
        'id' => 'delete-files',
        'class' => 'btn btn-danger btn-block-m',
        'data' => [
            'confirm' => Yii::t('app', 'Are you sure you want to delete this item(s)?'),
            'method' => 'get'
        ],
    ],
];

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= weebz\yii2basics\widgets\DefaultButtons::widget(['controller' => 'Folder','model'=>$model]) ?>
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
          <h3 class="card-title"><?= Yii::t('app','Add File');?></h3>
      </div>

      <div class="card-body">
          <div class="row">
              <div class="col-md-12">
                  <?= app\widgets\UploadFiles::widget(
                          [
                              'folder_id'=>$model->id,
                              'show_list'=>1,
                              'auto'=>0,
                              'show_upload_label'=>1,
                              'callback'=>'$.pjax.reload({container: "#list-files-grid", async: true,timeout : false});'
                         ]); ?>
              </div>
          </div>
          <!--.row-->
      </div>
      <!--.card-->
    </div>
    <!--.card-->
    
    <div class="card" id="list-files"> 
        
      <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app','List File');?></h3>
      </div>

        <div class="card-body">

            <div id='overlay-files' class='overlay' style='display:none;height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                <div class='d-flex align-items-center'>
                <strong> <?=Yii::t('app','Loading...')?></strong>
                <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= weebz\yii2basics\widgets\DefaultButtons::widget(
                                [
                                    'controller' => 'File',
                                    'show' => [],
                                    'extras'=>  $delete_files_button
                                ]
                            )
                        ?>
                    </p>
                    
                    <?php Pjax::begin(['id' => 'list-files-grid']) ?>
                    <?=     yii\grid\GridView::widget([
                        'dataProvider' =>  $dataProviderFolders,
                        'columns' => [
                            [
                                'headerOptions' => ['style' => 'width:1%'],
                                'header' => '',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::checkbox('file_selected[]', false, ['value' => $data->id, 'class' => 'file-item']);
                                },
                                //'visible'=> ControllerCommon::getPermission('file','delete-files')
                            ],
                            [
                                'headerOptions' => ['style' => 'width:4%'],
                                'attribute'=>'folder_id',
                                'format'=>'raw',
                                'value'=> function($data){
                                    if($data->folder_id != null)
                                        return Html::a($data->folder->name,Url::toRoute([Yii::getAlias('@web/folder/view'), 'id' => $data->folder_id]));
                                }
                            ],
                            [
                                'headerOptions' => ['style' => 'width:20%'],
                                'attribute'=>'description',
                                'label' => Yii::t('app', 'Description'),
                            ],
                            //'path',
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'header' => 'Preview',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a("<img class='brand-image img-circle elevation-3' width='50' src='/dummy/code.php?x=150x150/fff/000.jpg&text=FOLDER' />","/folder/$data->id",
                                    ['class'=>'btn btn-outline-secondary',"data-fancybox "=>"", "title"=>\Yii::t('app','View')]);
                                }
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute'=>'extension',
                                'label' => Yii::t('app', 'Extension'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute'=>'size',
                                'format' => 'bytes',
                                'label' => Yii::t('app', 'Size'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute'=>'duration',
                                'format' => 'time',
                                'label' => Yii::t('app', 'Duration'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:25%'],
                                'attribute'=>'created_at',
                                'format' => 'date',
                                'label' => Yii::t('app', 'Created At'),
                            ],
                            ['class' => \weebz\yii2basics\components\gridview\ActionColumn::class,
                                'headerOptions' => ['style' => 'width:10%'],
                                'template' => '{view}{remove}{delete}',
                                'path'=>'app',
                                'controller'=>'folder',
                                'buttons' => [  
                                    'remove' => function ($url, $model, $key) {
                                        return ControllerCommon::getPermission('file','remove-file') ? 
                                                Html::a('<i class="fas fa-unlink"></i>', yii\helpers\Url::to(['file/remove-file', 'id' => $model->id, 'folder' => $model->folder_id]),
                                                        ['class'=>'btn btn-outline-secondary',"data-toggle"=>"tooltip","data-placement"=>"top", "title"=>\Yii::t('app','Remove from folder')]) : '';
                                    },    
                                ]
                            ],
                        ],
                    ]); ?>
                    <?=     yii\grid\GridView::widget([
                        'dataProvider' =>  $dataProvider,
                        
                        'columns' => [
                            [
                                'headerOptions' => ['style' => 'width:1%'],
                                'header' => '',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::checkbox('file_selected[]', false, ['value' => $data->id, 'class' => 'file-item']);
                                },
                                //'visible'=> ControllerCommon::getPermission('file','delete-files')
                            ],
                            [
                                'headerOptions' => ['style' => 'width:4%'],
                                'attribute'=>'folder_id',
                                'format'=>'raw',
                                'value'=> function($data){
                                    if($data->folder_id != null)
                                        return Html::a($data->folder->name,Url::toRoute([Yii::getAlias('@web/folder/view'), 'id' => $data->folder_id]));
                                }
                            ],
                            [
                                'headerOptions' => ['style' => 'width:20%'],
                                'attribute'=>'description',
                                'label' => Yii::t('app', 'Description'),
                            ],
                            //'path',
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'header' => 'Preview',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    if ($data->urlThumb) {
                                        $url = Yii::getAlias('@web') . $data->urlThumb;
                                        return Html::a("<img class='brand-image img-circle elevation-3' width='50' src='{$url}' />",Yii::getAlias('@web').$data->url,
                                        ['class'=>'btn btn-outline-secondary',"data-fancybox "=>"", "title"=>\Yii::t('app','View')]);
                                    } else {
                                        return Html::a("<img class='brand-image img-circle elevation-3' width='50' src='/dummy/code.php?x=150x150/fff/000.jpg&text=NO PREVIEW' />",Yii::getAlias('@web').$data->url,
                                        ['class'=>'btn btn-outline-secondary',"data-fancybox "=>"", "title"=>\Yii::t('app','View')]);
                                    }
                                }
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute'=>'extension',
                                'label' => Yii::t('app', 'Extension'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute'=>'size',
                                'format' => 'bytes',
                                'label' => Yii::t('app', 'Size'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:10%'],
                                'attribute'=>'duration',
                                'format' => 'time',
                                'label' => Yii::t('app', 'Duration'),
                            ],
                            [
                                'headerOptions' => ['style' => 'width:25%'],
                                'attribute'=>'created_at',
                                'format' => 'date',
                                'label' => Yii::t('app', 'Created At'),
                            ],
                            ['class' => \weebz\yii2basics\components\gridview\ActionColumn::class,
                                'headerOptions' => ['style' => 'width:10%'],
                                'template' => '{view}{remove}{delete}',
                                'path'=>'app',
                                'controller'=>'file',
                                'buttons' => [  
                                    'remove' => function ($url, $model, $key) {
                                        return ControllerCommon::getPermission('file','remove-file') ? 
                                                Html::a('<i class="fas fa-unlink"></i>', yii\helpers\Url::to(['file/remove-file', 'id' => $model->id, 'folder' => $model->folder_id]),
                                                        ['class'=>'btn btn-outline-secondary',"data-toggle"=>"tooltip","data-placement"=>"top", "title"=>\Yii::t('app','Remove from folder')]) : '';
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