<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use yii\web\Response;

class ExportSimpleWidget extends Widget
{
    public $dataProvider;
    public $columns;
    public $filename = 'exported_data';
    public $formats = ['csv', 'excel', 'pdf'];
    public $labelMap = ['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'];
    public $exportTrigger = 'export';

    public function init()
    {
        parent::init();
        if (!$this->dataProvider || !$this->columns) {
            throw new InvalidConfigException("'dataProvider' e 'columns' são obrigatórios.");
        }
    }

    public function run()
    {
        $request = Yii::$app->request;
        $trigger = $request->get($this->exportTrigger);

        if (in_array($trigger, $this->formats, true)) {
            Yii::$app->controller->layout = false;
            Yii::$app->response->format = Response::FORMAT_RAW;
            $filename = $request->get('filename', $this->filename);

            // Coleta dados
            $data = [];
            $columnKeys = [];
            $columnLabels = [];

            foreach ($this->dataProvider->getModels() as $model) {
                $row = [];
                foreach ($this->columns as $column) {
                    $attribute = null;
                    $value = null;
                    $label = null;

                    if (is_array($column)) {
                        $attribute = $column['attribute'] ?? null;
                        $value = $column['value'] ?? null;
                        $label = $column['label'] ?? $attribute;
                    } elseif (is_string($column)) {
                        $parts = explode(':', $column);
                        $attribute = $parts[0] ?? null;
                        $label = $parts[2] ?? $attribute;
                    }

                    if ($attribute) {
                        $columnKeys[] = $attribute;
                        $columnLabels[$attribute] = $label;
                        $row[$attribute] = is_callable($value)
                            ? call_user_func($value, $model)
                            : ArrayHelper::getValue($model, $attribute);
                    }
                }
                $data[] = $row;
            }

            $columnKeys = array_values(array_unique($columnKeys));

            // Exporta CSV
            if ($trigger === 'csv') {
                $temp = tmpfile();
                $meta = stream_get_meta_data($temp);
                $path = $meta['uri'];

                $fp = fopen($path, 'w');
                fputcsv($fp, array_map(fn($key) => $columnLabels[$key] ?? $key, $columnKeys));
                foreach ($data as $row) {
                    $line = [];
                    foreach ($columnKeys as $key) {
                        $line[] = $row[$key] ?? '';
                    }
                    fputcsv($fp, $line);
                }
                fclose($fp);
                $content = file_get_contents($path);
                fclose($temp);

                return Yii::$app->response->sendContentAsFile(
                    $content,
                    $filename . '.csv',
                    ['mimeType' => 'text/csv', 'inline' => false]
                );
            }

            // Exporta Excel
            if ($trigger === 'excel') {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray([array_map(fn($key) => $columnLabels[$key] ?? $key, $columnKeys)], null, 'A1');

                foreach ($data as $i => $row) {
                    $line = [];
                    foreach ($columnKeys as $key) {
                        $line[] = $row[$key] ?? '';
                    }
                    $sheet->fromArray([$line], null, 'A' . ($i + 2));
                }

                $temp = tmpfile();
                $meta = stream_get_meta_data($temp);
                $path = $meta['uri'];

                $writer = new Xlsx($spreadsheet);
                $writer->save($path);
                $content = file_get_contents($path);
                fclose($temp);

                return Yii::$app->response->sendContentAsFile(
                    $content,
                    $filename . '.xlsx',
                    [
                        'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'inline' => false
                    ]
                );
            }

            // Exporta PDF
            if ($trigger === 'pdf') {
                $html = '<h2>' . Html::encode($filename) . '</h2><table border="1" cellpadding="5"><thead><tr>';
                foreach ($columnKeys as $header) {
                    $html .= '<th>' . Html::encode($columnLabels[$header] ?? $header) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                foreach ($data as $row) {
                    $html .= '<tr>';
                    foreach ($columnKeys as $key) {
                        $html .= '<td>' . Html::encode($row[$key] ?? '') . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';

                $mpdf = new Mpdf();
                $mpdf->WriteHTML($html);
                $pdfContent = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

                return Yii::$app->response->sendContentAsFile(
                    $pdfContent,
                    $filename . '.pdf',
                    ['mimeType' => 'application/pdf', 'inline' => false]
                );
            }

            Yii::$app->end();
        }

        // Render botões
        $buttons = [];
        foreach ($this->formats as $format) {
            $url = Url::current([
                $this->exportTrigger => $format,
                'filename' => $this->filename,
            ]);
            $buttons[] = Html::a(
                $this->labelMap[$format] ?? strtoupper($format),
                $url,
                [
                    'class' => 'btn btn-outline-secondary me-2',
                    'target' => '_blank',
                    'data-pjax' => '0',
                ]
            );
        }

        return Html::tag('div', implode("\n", $buttons), ['class' => 'export-button-group']);
    }
}
