<?php

use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SiteSectionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Site Sections');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-body">
                    <div class="row mb-2">
                    <div class="col-md-12">
                            <?= weebz\yii2basics\widgets\DefaultButtons::widget(
                            [
                                'controller' => Yii::$app->controller->id,'show' => ['create'],'verGroup'=>false
                            ]) ?>
                        </div>
                    </div>

                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
                    <div id='overlay-site-section' class='overlay' style='display:none;height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                        <div class='d-flex align-items-center'>
                            <strong> <?= Yii::t('app', 'Loading...') ?></strong>
                            <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                        </div>
                    </div>

                    <?php Pjax::begin(['id'=>'grid-site-section']); ?>
                        <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            'name',
                            'order',
                            'status:boolean',
                            [
                                'class' => weebz\yii2basics\components\gridview\ActionColumn::class,
                                'template' => '{status} {view} {update} {remove}',
                                'order'=>true,
                                'orderModel'=>'SiteSection',
                                'orderField'=>'order',
                            ]
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                            'class' => 'yii\bootstrap5\LinkPager',
                        ]
                    ]); ?>
                   <?php Pjax::end() ?>

                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
