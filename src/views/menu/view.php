<?php

use weebz\yii2basics\components\gridview\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Menu */

$this->title = $model->label;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);

$script = <<< JS

    $(function(){

        Fancybox.bind("[data-fancybox]");

        jQuery("#list-item-menu .table tbody").sortable({
            update: function(event, ui) {
                let items  = [];
                let i = 0;
                $('#overlay').show();
                $( "#list-item-menu .table tbody tr" ).each(function( index ) {
                    items[items.length] = $( this ).attr("data-key");
                });
                
                $.ajax({
                    method: "POST",
                    url: '/menu/order-menu',
                    data: {'items':items}
                }).success(function(response) {        
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

$assetsDir = ControllerCommon::$assetsDir;

$this::registerJs($script, $this::POS_END);
$this::registerJsFile($assetsDir . '/plugins/jquery-ui/jquery-ui.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this::registerCssFile($assetsDir . '/plugins/jquery-ui/jquery-ui.min.css');

// $MP = new MercadoPago('TEST-3935825493019834-122811-a58b6ebfb2ce4572be4dec4a221a1f2c-25239504');
// // echo "ADD PAYMENT\n";
// dd($MP->addPayment());
// // echo "VER PAYMENTs\n";
// //dd($MP->getPayments());
// die();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= weebz\yii2basics\widgets\DefaultButtons::widget([
                            'controller' => Yii::$app->controller->id,'model'=>$model,'verGroup'=>false
                        ]) ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute'=>'menu_id',
                                'format'=>'raw',
                                'value'=> function($model){
                                    if($model->menu_id != null)
                                        return Html::a($model->menu->label,Url::toRoute([Yii::getAlias('@web/menu/view'), 'id' => $model->menu_id]));
                                }
                            ],
                            'label',
                            'icon',
                            'visible',
                            'url:url',
                            'active',
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
    <?php if($model->url == '#'):?>              
    <div class="row" id="list-item-menu">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a('<i class="fas fa-plus-square"></i> ' . Yii::t('app', 'Add Item'), ['create','id'=>$model->id], ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            'label',
                            'icon',
                            'order',
                            //'visible',
                            [
                                'attribute'=> 'url',
                                'header'=> Yii::t('app','Type'),
                                'value'=> function($data) {
                                    return empty($data->url) || $data->url == '#' ? 'Sub-Menu' : 'Item';
                                }
                            ],
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
    <?php endif;?>
</div>