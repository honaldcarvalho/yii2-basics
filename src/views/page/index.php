<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\PageSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Pages';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a(Yii::t('app', 'Create Page'), ['create'], ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>


                    <?php Pjax::begin(); ?>
                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            'id',
                            'section.name:text:Seção',
                            'slug',
                            'title',
                            'description',
                            //'content:ntext',
                            //'keywords:ntext',
                            [
                                'attribute'=>'created_at',
                                'format' => 'date',
                                'label' => Yii::t('app', 'Created at'),
                                'filter' =>Html::input('date', ucfirst(Yii::$app->controller->id).'Search[created_at]',$searchModel->created_at,['class'=>'form-control dateandtime'])
                            ],
                            [
                                'attribute'=>'updated_at',
                                'format' => 'date',
                                'label' => Yii::t('app', 'Updated at'),
                                'filter' =>Html::input('date',ucfirst(Yii::$app->controller->id).'Search[updated_at]',$searchModel->updated_at,['class'=>'form-control dateandtime'])
                            ],
                            'status:boolean',
                            ['class'=>weebz\yii2basics\components\gridview\ActionColumn::class,]
                            
                        ],
                    ]); ?>

                    <?php Pjax::end(); ?>

                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
