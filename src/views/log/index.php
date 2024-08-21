<?php

use weebz\yii2basics\models\User;
use weebz\yii2basics\models\Rule;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel weebz\yii2basics\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            'id',
                            [   
                                'attribute'=>'user_id',
                                'header'=>Yii::t('app','User'),
                                'filter'=>yii\helpers\ArrayHelper::map(User::find()->asArray()->all(), 'id', 'fullname'),
                                'value'=> function($data){return $data->user->fullname;}
                            ],
                            [   
                                'attribute'=>'controller',
                                'header'=>Yii::t('app','Controller'),
                                'filter'=> Html::dropDownList('LogSearch[controller]',null,(new Rule)->getControllers()['controllers_array'],['class'=>'form-control']) ,
                                //'filter'=> yii\helpers\ArrayHelper::map((new Rule)->getControllers()['controllers_array'], 'id', 'name') ,
                                'value'=> function($data){return $data->controller;}
                            ],
                            'controller',
                            'action',
                            'data:ntext',
                            'created_at:datetime',

                            ['class' =>'weebz\yii2basics\components\gridview\ActionColumn',],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                            'class' => 'yii\bootstrap5\LinkPager',
                        ]
                    ]); ?>


                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
