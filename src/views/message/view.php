<?php

use weebz\yii2basics\widgets\DefaultButtons;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Message */

$this->title = Yii::t('app',"Message #{$model->id}");
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Messages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= DefaultButtons::widget(
                                [
                                    'controller' => Yii::$app->controller->id,'model'=>$model,'verGroup'=>false
                                ]) ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'language',
                            'translation:ntext',
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