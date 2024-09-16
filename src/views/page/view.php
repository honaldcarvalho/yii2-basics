<?php

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\widgets\ListFiles;
use weebz\yii2basics\widgets\StorageUploadMultiple;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Page */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= \weebz\yii2basics\widgets\DefaultButtons::widget(['controller' => 'Page','model'=>$model]) ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'section.name:text:'.Yii::t('app', 'Section'),
                            'slug',
                            'title',
                            'description',
                            'content:raw',
                            'keywords:ntext',
                            'created_at:datetime',
                            'status:boolean',
                        ],
                    ]) ?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
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
                        'group_id' => AuthController::userGroup(),
                        'attact_model'=>[
                            'class_name'=> 'weebz\\yii2basics\\models\\PageFile',
                            'id'=> $model->id,
                            'fields'=> ['page_id','file_id']
                        ],
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
    
    <?= ListFiles::widget([
        'dataProvider' => $dataProvider,
    ]); ?>
</div>