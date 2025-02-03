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

        // Topological sort without ignoring cycles
        $sortedTables = $this->topologicalSortWithCycles($dependencies);

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

        // Disable foreign key checks
        $db->createCommand("SET FOREIGN_KEY_CHECKS = 0;")->execute();

        foreach ($tables as $table) {
            $db->createCommand("TRUNCATE TABLE `{$table->name}`")->execute();
            echo "Cleared table: {$table->name}\n";
        }

        // Enable foreign key checks
        $db->createCommand("SET FOREIGN_KEY_CHECKS = 1;")->execute();

        echo "Database cleared successfully.\n";
    }

    public function actionImport($inputFile)
    {
        /** @var Connection $db */
        $db = \Yii::$app->db;

        if (!file_exists($inputFile)) {
            echo "File {$inputFile} does not exist.\n";
            return;
        }

        $handle = fopen($inputFile, "r");
        if ($handle === false) {
            echo "Unable to open file: {$inputFile}\n";
            return;
        }

        $sql = '';
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '' || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue; // Skip empty lines and comments
            }

            $sql .= " " . $line;

            if (substr(trim($line), -1) === ';') {
                try {
                    $db->createCommand($sql)->execute();
                    echo "Executed: {$sql}\n";
                } catch (\yii\db\Exception $e) {
                    echo "Error executing: {$sql}\n";
                    echo "Error: " . $e->getMessage() . "\n";
                }
                $sql = '';
            }
        }

        fclose($handle);
        echo "Import completed successfully.\n";
    }

    private function topologicalSortWithCycles($dependencies)
    {
        $sorted = [];
        $visited = [];
        $tempMarked = [];

        $visit = function ($node) use (&$visit, &$sorted, &$visited, &$tempMarked, $dependencies) {
            if (isset($visited[$node])) {
                return;
            }

            if (isset($tempMarked[$node])) {
                // Cycle detected; process node anyway
                return;
            }

            $tempMarked[$node] = true;

            foreach ($dependencies[$node] as $dep) {
                $visit($dep);
            }

            $visited[$node] = true;
            $sorted[] = $node;

            unset($tempMarked[$node]);
        };

        foreach (array_keys($dependencies) as $node) {
            $visit($node);
        }

        return array_reverse($sorted);
    }
}
