<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Page */

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
</div>