<?php

use yii\grid\GridView;
use weebz\yii2basics\widgets\DefaultButtons;
use weebz\yii2basics\components\gridview\ActionColumn;
use weebz\yii2basics\controllers\ AuthController;

/* @var $this yii\web\View */
/* @var $searchModel weebz\yii2basics\models\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Menus');
$this->params['breadcrumbs'][] = $this->title;

$script = <<< JS

    $(function(){

        Fancybox.bind("[data-fancybox]");

        jQuery(".table tbody").sortable({
            update: function(event, ui) {
                let items  = [];
                let i = 0;
                $('#overlay').show();
                $( ".table tbody tr" ).each(function( index ) {
                    items[items.length] = $( this ).attr("data-key");
                });
                
                $.ajax({
                    method: "POST",
                    url: '/menu/order-menu',
                    data: {'items':items}
                }).done(function(response) {        
                    toastr.success("atualizado");
                }).fail(function (response) {
                    toastr.error("Error ao atualizar a ordem. Recarregue a pagina");
                }).always(function (response) {
                    $('#overlay').hide();
                });

            }
        });

    });
  
JS;

$this::registerJs($script, $this::POS_END);

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
                                'controller' => Yii::$app->controller->id,'show'=>['create'],'verGroup'=>false
                            ]) ?>
                        </div>
                    </div>

                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            'menu.label:text:Menu',
                            'label',
                            'icon',
                            'order',
                            //'visible',
                            'url:url',
                            //'active',
                            'status:boolean',

                            ['class' => ActionColumn::class,'verGroup'=>false],
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
