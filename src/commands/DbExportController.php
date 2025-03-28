<?php

namespace app\commands;

use yii\console\Controller;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

class DbExportController extends Controller
{
    public function actionExport($outputFile = 'export.sql',$exclude = '', $only ='')
    {
        /** @var Connection $db */
        $db = \Yii::$app->db;
        $schema = $db->schema;
        $exclude_arr = explode(',',$exclude);
        $tables = $schema->getTableSchemas();
        if(!empty($only)){
            $tables =  explode(',',$only);
        }
        $sql = "SET FOREIGN_KEY_CHECKS = 0;\n"; // Disable foreign key checks for import
        foreach ($tables as $table) {
            if(!in_array($table,$exclude_arr)){
                $rows = $db->createCommand("SELECT * FROM {$table->name}")->queryAll();
                if ($rows) {
                    $columns = array_keys($rows[0]);
                    foreach ($rows as $row) {
                        $values = array_map(function ($value) use ($db) {
                            return $value === null ? 'NULL' : $db->quoteValue($value);
                        }, $row);
                        $sql .= "INSERT INTO `{$table->name}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n"; // Re-enable foreign key checks

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
}
