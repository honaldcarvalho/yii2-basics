<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= "<?= \weebz\yii2basics\widgets\DefaultButtons::widget(['controller' => '".StringHelper::basename($generator->modelClass)."', 
                            'show' => ['create'], 
                            'buttons_name' => ['create' => Yii::t('app','Create ".Inflector::camel2words(StringHelper::basename($generator->modelClass))."')],
                            'verGroup' => true,
                            ]) ?>"?>
                        </div>
                    </div>


                    <?= $generator->enablePjax ? "<?php Pjax::begin(); ?>\n" : '' ?>
                    <?php if (!empty($generator->searchModelClass)): ?>
                    <?= "<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?> echo $this->render('/_parts/filter', ['view' =>"/<?=Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>",'searchModel' => $searchModel]); ?>
                    <?php endif; ?>
                    <?php if ($generator->indexWidgetType === 'grid'): ?>
                        <?= " <?= " ?>GridView::widget(['dataProvider' => $dataProvider,
                        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n                        'columns' => [\n" : "'columns' => [\n"; ?>
                        ['class' => 'yii\grid\SerialColumn'],

                        <?php
                        $count = 0;
                        if (($tableSchema = $generator->getTableSchema()) === false) {
                            foreach ($generator->getColumnNames() as $name) {
                                if (++$count < 6) {
                                    echo "                            '" . $name . "',\n";
                                } else {
                                    echo "                            //'" . $name . "',\n";
                                }
                            }
                        } else {
                            foreach ($tableSchema->columns as $column) {
                                $format = $generator->generateColumnFormat($column);
                                if (++$count < 6) {
                                    echo "                            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                                } else {
                                    echo "                            //'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                                }
                            }
                        }
                        ?>

                        ['class' => 'weebz\yii2basics\components\gridview\ActionColumn'],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                        'class' => 'yii\bootstrap5\LinkPager',
                        ]
                        ]); ?>
                    <?php else: ?>
                        <?= "               <?= " ?>ListView::widget([
                        'dataProvider' => $dataProvider,
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'itemOptions' => ['class' => 'item'],
                        'itemView' => function ($model, $key, $index, $widget) {
                        return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
                        },
                        'pager' => [
                        'class' => 'yii\bootstrap5\LinkPager',
                        'options' => ['class' => 'pagination mt-3'],
                        ]
                        ]) ?>
                    <?php endif; ?>

                    <?= $generator->enablePjax ? "                    <?php Pjax::end(); ?>\n" : '' ?>

                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>