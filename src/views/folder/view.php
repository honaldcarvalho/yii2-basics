<?php

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\models\Folder;
use weebz\yii2basics\widgets\AppendModel;
use weebz\yii2basics\widgets\ListFiles;
use yii\helpers\Html;
use yii\widgets\DetailView;
use weebz\yii2basics\widgets\StorageUploadMultiple;
use yii\helpers\ArrayHelper;
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

    <?= AppendModel::widget([
        'title'=>'Folders',
        'attactModel'=>'Folder',
        'controller'=>'folder',
        'attactClass'=>'weebz\\yii2basics\\models\\Folder',
        'dataProvider' => new \yii\data\ActiveDataProvider([
            'query' => $model->getFolders(),
        ]),
        'showFields'=>['folder.name','folder.description','folder.status:boolean'],
        'fields'=>
        [
            [   
                'name'=>'folder_id',
                'type'=>'hidden',
                'value'=>$model->id
            ],
            [
                'name'=>'name',
                'type'=>'text',
            ],
            [
                'name'=>'description',
                'type'=>'text',
            ],
            [
                'name'=>'status',
                'type'=>'checkbox'
            ],
        ]
    ]); ?>
    <!--.row-->

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
    
    <?= ListFiles::widget([
        'dataProvider' => $dataProvider,
    ]); ?>
</div>