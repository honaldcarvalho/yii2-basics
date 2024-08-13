<?php

use weebz\yii2basics\widgets\DefaultButtons;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel weebz\yii2basics\models\LanguageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Languages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                        <?= DefaultButtons::widget(
                            [
                                'controller' => Yii::$app->controller->id,
                                'show'=>['create'],'buttons_name'=>['create'=>Yii::t("backend","Create Language")]
                            ]) ?>
                        </div>
                    </div>


                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [

                            'id',
                            'code',
                            'name',
                            'status:boolean',

                            ['class' =>'weebz\yii2basics\components\gridview\ActionColumn',],
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
