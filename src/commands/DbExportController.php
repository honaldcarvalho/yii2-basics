<?php

namespace weebz\yii2basics\commands;

use yii\console\Controller;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

class DbExportController extends Controller
{
    public function actionExport($outputFile = 'export.sql')
    {
        /** @var Connection $db */
        $db = \Yii::$app->db;
        $schema = $db->schema;

        $tables = $schema->getTableSchemas();
        $dependencies = [];

        // Build dependency graph
        foreach ($tables as $table) {
            $dependencies[$table->name] = [];
            foreach ($table->foreignKeys as $fk) {
                if (isset($fk[0])) {
                    $dependencies[$table->name][] = $fk[0];
                }
            }
        }

        // Topological sort
        $sortedTables = $this->topologicalSort($dependencies);

        $sql = "";
        foreach ($sortedTables as $tableName) {
            $rows = $db->createCommand("SELECT * FROM {$tableName}")->queryAll();
            if ($rows) {
                $columns = array_keys($rows[0]);
                foreach ($rows as $row) {
                    $values = array_map(function ($value) use ($db) {
                        return $value === null ? 'NULL' : $db->quoteValue($value);
                    }, $row);
                    $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }

        file_put_contents($outputFile, $sql);
        echo "Database exported to {$outputFile}\n";
    }

    private function topologicalSort($dependencies)
    {
        $sorted = [];
        $visited = [];

        $visit = function ($node) use (&$visit, &$sorted, &$visited, $dependencies) {
            if (isset($visited[$node])) {
                if ($visited[$node] === 'visiting') {
                    throw new \Exception("Cyclic dependency detected at {$node}");
                }
                return;
            }
            $visited[$node] = 'visiting';
            foreach ($dependencies[$node] as $dep) {
                $visit($dep);
            }
            $visited[$node] = 'visited';
            $sorted[] = $node;
        };

        foreach (array_keys($dependencies) as $node) {
            $visit($node);
        }

        return array_reverse($sorted);
    }
}
