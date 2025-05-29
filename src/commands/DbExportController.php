<?php

namespace weebz\yii2basics\commands;

use yii\console\Controller;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

class DbExportController extends Controller
{
    public $outputFile = 'export.sql';
    public $exclude = '';
    public $only = '';
    public $replace = false;

    public function options($actionID)
    {
        return [
            'outputFile',
            'exclude',
            'only',
            'replace',
        ];
    }

    public function optionAliases()
    {
        return [
            'f' => 'outputFile',
            'e' => 'exclude',
            'o' => 'only',
            'r' => 'only',
        ];
    }

    public function actionExport()
    {
        /** @var Connection $db */
        $db = \Yii::$app->db;
        $schema = $db->schema;
        $exclude_arr = explode(',',$this->exclude);

        $sql = "SET FOREIGN_KEY_CHECKS = 0;\n"; // Disable foreign key checks for import

        if($this->replace){
            $function = 'REPLACE';
        } else {
            $function = 'INSERT';
        }

        if(!empty($this->only)){
            $tables =  explode(',',$this->only);
            foreach ($tables as $table) {
                if(!in_array($table,$exclude_arr)){
                    $rows = $db->createCommand("SELECT * FROM {$table}")->queryAll();
                    if ($rows) {
                        $columns = array_keys($rows[0]);
                        foreach ($rows as $row) {
                            $values = array_map(function ($value) use ($db) {
                                return $value === null ? 'NULL' : $db->quoteValue($value);
                            }, $row);
                            $sql .= "{$function} INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                        }
                    }
                }
            }
        } else {
            $tables = $schema->getTableSchemas();
            foreach ($tables as $table) {
                $rows = $db->createCommand("SELECT * FROM {$table->name}")->queryAll();
                if ($rows) {
                    $columns = array_keys($rows[0]);
                    foreach ($rows as $row) {
                        $values = array_map(function ($value) use ($db) {
                            return $value === null ? 'NULL' : $db->quoteValue($value);
                        }, $row);
                        $sql .= "{$function} INTO `{$table->name}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n"; // Re-enable foreign key checks

        file_put_contents($this->outputFile, $sql);
        echo "Database exported to {$this->outputFile}\n";
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
