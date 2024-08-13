<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Param */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Params'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= weebz\yii2basics\widgets\DefaultButtons::widget(['controller' => 'Params','model'=>$model,'verGroup'=>false]) ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'description',
                            [
                                'attribute'=>'file_id',
                                'format'=>'raw',
                                'value'=> function($data){
                                    if(!empty($data->file_id)){
                                        $url = Yii::getAlias('@web').$data->file->urlThumb;
                                        return "<img class='brand-image img-circle elevation-3' width='50' src='{$url}' />";
                                    }
                                }
                            ],
                            'meta_viewport',
                            'meta_author',
                            'meta_robots',
                            'meta_googlebot',
                            'meta_keywords',
                            'meta_description',
                            'canonical',
                            'host',
                            'title',
                            'bussiness_name',
                            'email:email',
                            'fone',
                            'address',
                            'postal_code',
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
        <!--.card-body-->
    </div>
    <!--.card-->
</div>