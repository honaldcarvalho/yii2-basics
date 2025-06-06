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
        $columnKeys = [];

        foreach ($this->dataProvider->getModels() as $model) {
            $row = [];
            foreach ($this->columns as $column) {
                $attribute = null;
                $value = null;

                if (is_array($column)) {
                    $attribute = $column['attribute'] ?? null;
                    $value = $column['value'] ?? null;
                } elseif (is_string($column)) {
                    // Parse "attribute:format:label"
                    $parts = explode(':', $column);
                    $attribute = $parts[0] ?? null;
                }

                if ($attribute) {
                    $columnKeys[] = $attribute;
                    if (is_callable($value)) {
                        $row[$attribute] = call_user_func($value, $model);
                    } else {
                        $row[$attribute] = ArrayHelper::getValue($model, $attribute);
                    }
                }
            }
            $exportData[] = $row;
        }

        $columnKeys = array_values(array_unique($columnKeys));
        $json = Html::encode(json_encode($exportData));

        $buttons = [];
        foreach ($this->formats as $format) {
            $url = Url::to(array_merge($this->exportRoute, ['format' => $format, 'filename' => $this->filename]));
            $buttons[] = Html::beginForm($url, 'post', [
                    'target' => '_blank',
                    'style' => 'display:inline-block']
            ) .
                Html::hiddenInput('export_data', $json) .
                Html::hiddenInput('export_columns', json_encode($columnKeys)) .
                Html::submitButton(
                    $this->labelMap[$format] ?? strtoupper($format),
                    ['class' => 'btn btn-outline-secondary me-2']
                ) .
                Html::endForm();
        }

        return Html::tag('div', implode("\n", $buttons), ['class' => 'export-button-group']);
    }
}