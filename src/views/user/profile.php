<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\modules\common\models\User $model */

$this->title = Yii::t('app', 'View User: {name}', [
    'name' => $model->fullname,
]);

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                
                <p>
                <?= Html::a(Yii::t('app', '<i class="fas fa-edit"></i>&nbsp; Edit'), ['edit', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                </p>
                <div class="col-md-3">
                    <?=
                    app\widgets\FileInputModel::widget(
                        [
                            'model' => 'User',
                            'model_field' => 'file_id',
                            'model_id' => $model->id ?? null,
                            'value' => $model->file_id ?? null,
                            'preview' => $model->file->url  ?? null,
                            'field_id' => 'user-file_id',
                            'field_name' => 'User[file_id]',
                            'style_class'=>'img-circle elevation-2',
                            'label' => Yii::t('app','Picture'),
                            'folder_id' => 3,
                            //'action' => 'file/send',
                            'as_type' => 0,
                            'save_file_model' => 1,
                            'aspectRatio' => '1',
                            'extensions' => ['jpeg', 'jpg', 'png'],
                        ]
                    );
                    ?>
                </div>
                <div class="col-md-9">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'fullname',
                            'username',
                            'email:email',
                            'phone',
                            'status:boolean',
                        ],
                    ]) ?>
                    
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>
<!--.container-->
    