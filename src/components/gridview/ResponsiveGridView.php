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
        text-align: right; /* conteúdo alinhado à direita */
        white-space: normal;
        border: none !important;
        min-height: 2.5em;
    }

    td::before {
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
}
CSS;


        Yii::$app->view->registerCss($css);
    }
}
