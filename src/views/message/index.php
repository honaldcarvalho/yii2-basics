<?php

use weebz\yii2basics\modules\common\models\SourceMessage;
use weebz\yii2basics\modules\common\widgets\DefaultButtons;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel weebz\yii2basics\modules\common\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Messages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                    <div class="col-md-12">
                            <?= weebz\yii2basics\modules\common\widgets\DefaultButtons::widget(
                                [
                                    'controller' => Yii::$app->controller->id,'verGroup'=>false,
                                    'show'=>['create'],'buttons_name'=>['create'=>Yii::t("backend","Create Language")]
                                ]) ?>
                        </div>
                    </div>


                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [   
                                'header'=> Yii::t('app','Source Message'),
                                'attribute'=>'playlist_id',
                                'filter'=> \kartik\select2\Select2::widget([
                                            'attribute' => 'id',
                                            'name'=>'MessageSearch[id]',
                                            'value'=>$searchModel->id,
                                            'data' => yii\helpers\ArrayHelper::map(SourceMessage::find()
                                                    ->asArray()->all(), 
                                                        'id', 'message'),
                                            'options' => ['placeholder' => Yii::t('app',' -- Select a Source --')],
                                            'pluginOptions' => [
                                                'allowClear' => true
                                            ],
                                        ]),
                                'value'=> function($data){ return $data->sourceMessage->message;},
                            ],  
                            'language',
                            'translation:ntext',

                            ['class' =>'weebz\yii2basics\modules\common\components\gridview\ActionColumn','verGroup'=>false],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
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
