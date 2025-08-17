<?php

namespace weebz\yii2basics\components;

use Yii;
use yii\data\DataProviderInterface;

class Exporter
{
    /** @param array<array{label:string,value:callable(\yii\base\Model):mixed}> $columns */
    public static function sendCsv(DataProviderInterface $dp, array $columns, string $filename = 'export'): void
    {
        // Desliga paginação para exportar tudo
        $dp->pagination = false;

        // Limpa buffers antes de header
        while (ob_get_level()) { ob_end_clean(); }

        $name = $filename . '_' . date('Ymd_His') . '.csv';
        $resp = Yii::$app->response;
        $resp->format = \yii\web\Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $resp->headers->set('Content-Disposition', "attachment; filename=\"$name\"");
        $out = fopen('php://output', 'w');

        // (Opcional) BOM para Excel
        fwrite($out, "\xEF\xBB\xBF");

        // Cabeçalho
        fputcsv($out, array_map(fn($c) => $c['label'], $columns));

        foreach ($dp->getModels() as $model) {
            $row = [];
            foreach ($columns as $col) {
                $val = ($col['value'])($model);
                // normaliza \n para uma linha
                $row[] = is_scalar($val) ? (string)$val : json_encode($val, JSON_UNESCAPED_UNICODE);
            }
            fputcsv($out, $row);
        }
        fclose($out);
        Yii::$app->end();
    }

    /** @param array<array{label:string,value:callable(\yii\base\Model):mixed}> $columns */
    public static function sendXml(DataProviderInterface $dp, array $columns, string $root = 'items', string $node = 'item', string $filename = 'export'): void
    {
        $dp->pagination = false;
        while (ob_get_level()) { ob_end_clean(); }

        $name = $filename . '_' . date('Ymd_His') . '.xml';
        $resp = Yii::$app->response;
        $resp->format = \yii\web\Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'application/xml; charset=UTF-8');
        $resp->headers->set('Content-Disposition', "attachment; filename=\"$name\"");

        $xml = new \SimpleXMLElement("<{$root}/>");
        foreach ($dp->getModels() as $model) {
            $nodeEl = $xml->addChild($node);
            foreach ($columns as $col) {
                $val = ($col['value'])($model);
                $nodeEl->addChild(self::safeTag($col['label']), htmlspecialchars(is_scalar($val) ? (string)$val : json_encode($val, JSON_UNESCAPED_UNICODE)));
            }
        }
        echo $xml->asXML();
        Yii::$app->end();
    }

    /** @param array<array{label:string,value:callable(\yii\base\Model):mixed}> $columns */
    public static function sendJson(DataProviderInterface $dp, array $columns, string $filename = 'export'): void
    {
        $dp->pagination = false;
        while (ob_get_level()) { ob_end_clean(); }

        $name = $filename . '_' . date('Ymd_His') . '.json';
        $resp = Yii::$app->response;
        $resp->format = \yii\web\Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $resp->headers->set('Content-Disposition', "attachment; filename=\"$name\"");

        $rows = [];
        foreach ($dp->getModels() as $model) {
            $row = [];
            foreach ($columns as $col) {
                $row[$col['label']] = ($col['value'])($model);
            }
            $rows[] = $row;
        }
        echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Yii::$app->end();
    }

    private static function safeTag(string $label): string
    {
        // transforma "Nº OS" → "N_OS", "Data/Hora" → "Data_Hora"
        $tag = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $label) ?: 'field';
        if (preg_match('/^[0-9]/', $tag)) $tag = "_{$tag}";
        return $tag;
    }
}
