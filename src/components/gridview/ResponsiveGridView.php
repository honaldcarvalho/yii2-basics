<?php

namespace weebz\yii2basics\components\gridview;

use Yii;
use yii\grid\GridView;

class ResponsiveGridView extends GridView
{
    public $dataColumnClass = ResponsiveDataColumn::class;

    public function init()
    {
        parent::init();

$css = <<<CSS
@media (max-width: 768px) {
    .grid-view table,
    .grid-view thead,
    .grid-view tbody,
    .grid-view th,
    .grid-view td,
    .grid-view tr {
        display: block;
        width: 100%;
    }

    .grid-view thead {
        display: none;
    }

    .grid-view tr {
        margin-bottom: 1rem;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        padding: 0.5rem;
    }

    .grid-view td {
        position: relative;
        padding-left: 50%;
        text-align: right;
        white-space: normal;
        border: none !important;
        min-height: 2.5em;
    }

    .grid-view td::before {
        content: attr(data-title);
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        padding-left: 10px;
        font-weight: bold;
        text-align: left;
        white-space: nowrap;
        color: #aaa;
    }

    .grid-view .hide-mobile {
        display: none !important;
    }
}
CSS;


        Yii::$app->view->registerCss($css);
    }
}
