<?php

namespace app\commands;

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

        // Topological sort with cycle resolution
        $sortedTables = $this->topologicalSortWithCycleResolution($dependencies);

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

    public function actionClear()
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

        // Reverse topological sort to delete child tables first
        $sortedTables = array_reverse($this->topologicalSortWithCycleResolution($dependencies));

        foreach ($sortedTables as $tableName) {
            $db->createCommand("DELETE FROM `{$tableName}`")->execute();
            echo "Cleared table: {$tableName}\n";
        }

        echo "Database cleared successfully.\n";
    }

    private function topologicalSortWithCycleResolution($dependencies)
    {
        $sorted = [];
        $visited = [];

        $visit = function ($node, &$stack) use (&$visit, &$sorted, &$visited, $dependencies) {
            if (isset($visited[$node])) {
                if ($visited[$node] === 'visiting') {
                    // Cycle detected, break it by ignoring the current dependency
                    return;
                }
                return;
            }
            $visited[$node] = 'visiting';
            $stack[] = $node;

            foreach ($dependencies[$node] as $dep) {
                if (!in_array($dep, $stack)) {
                    $visit($dep, $stack);
                }
            }

            array_pop($stack);
            $visited[$node] = 'visited';
            $sorted[] = $node;
        };

        foreach (array_keys($dependencies) as $node) {
            $stack = [];
            $visit($node, $stack);
        }

        return array_reverse($sorted);
    }
}
