<?php
namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\grid\GridView;

class ExportButtonGroup extends Widget
{
    public $dataProvider;
    public $columns;
    public $filename = 'Exported_Data';
    public $exportUrl = ['/site/export']; // endpoint para exportar os dados

    public function run()
    {
        $buttons = [];
        $formats = [
            'csv' => 'CSV',
            'excel' => 'Excel',
            'pdf' => 'PDF',
        ];

        foreach ($formats as $format => $label) {
            $buttons[] = Html::a(
                $label,
                array_merge($this->exportUrl, [
                    'format' => $format,
                    'filename' => $this->filename,
                ]),
                ['class' => 'btn btn-outline-primary me-2', 'target' => '_blank']
            );
        }

        return Html::tag('div', implode("\n", $buttons), ['class' => 'mb-3']);
    }
}
