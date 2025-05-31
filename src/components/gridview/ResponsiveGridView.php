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
    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }

    thead {
        display: none;
    }

    tr {
        margin-bottom: 1rem;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        padding: 0.5rem;
    }

    td {
        position: relative;
        padding-left: 50%;
        text-align: left;
        white-space: normal;
        border: none !important;
    }

    td::before {
        content: attr(data-title);
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: bold;
        color: #ccc;
    }
}
CSS;

        Yii::$app->view->registerCss($css);
    }
}
