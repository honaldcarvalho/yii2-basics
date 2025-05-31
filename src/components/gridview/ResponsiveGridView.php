<?php

namespace weebz\yii2basics\components\gridview;

use Yii;
use yii\grid\GridView;

$css = <<< CSS
@media (max-width: 768px) {
    table td::before {
        content: attr(data-title)!important;
        font-weight: bold!important;
        display: block!important;
    }
}
CSS;

$view = Yii::$app->view;
$view->registerCss($css);

class ResponsiveGridView extends GridView
{
    public $dataColumnClass = ResponsiveDataColumn::class;
}
