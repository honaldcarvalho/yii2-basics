<?php

namespace weebz\yii2basics\widgets;


use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\base\InvalidConfigException;

class ExportSimpleWidget extends Widget
{
    public $dataProvider;
    public $columns;
    public $filename = 'exported_data';
    public $formats = ['csv', 'excel', 'pdf'];
    public $labelMap = ['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'];
    public $exportRoute = ['/site/export'];

    public function init()
    {
        parent::init();
        if (!$this->dataProvider || !$this->columns) {
            throw new InvalidConfigException("'dataProvider' e 'columns' são obrigatórios.");
        }
    }

    public function run()
    {
        $exportData = [];
        foreach ($this->dataProvider->getModels() as $model) {
            $row = [];
            foreach ($this->columns as $column) {
                if (is_array($column)) {
                    $key = $column['attribute'] ?? null;
                    $value = $column['value'] ?? null;
                    if ($key && is_callable($value)) {
                        $row[$key] = call_user_func($value, $model);
                    } elseif ($key) {
                        $row[$key] = ArrayHelper::getValue($model, $key);
                    }
                } else {
                    $row[$column] = ArrayHelper::getValue($model, $column);
                }
            }
            $exportData[] = $row;
        }

        $json = Html::encode(json_encode($exportData));
        $columns = array_map(function($col) {
            return is_array($col) ? ($col['attribute'] ?? '') : $col;
        }, $this->columns);

        $buttons = [];
        foreach ($this->formats as $format) {
            $url = Url::to(array_merge($this->exportRoute, ['format' => $format, 'filename' => $this->filename]));
            $id = 'export-btn-' . $format . '-' . $this->getId();
            $buttons[] = Html::beginForm($url, 'post', ['target' => '_blank', 'style' => 'display:inline-block']) .
                Html::hiddenInput('export_data', $json) .
                Html::hiddenInput('export_columns', json_encode($columns)) .
                Html::submitButton($this->labelMap[$format] ?? strtoupper($format), ['class' => 'btn btn-outline-secondary me-2']) .
                Html::endForm();
        }

        return Html::tag('div', implode("\n", $buttons), ['class' => 'export-button-group']);
    }
}
