<?php 

namespace app\commands;

use yii\console\Controller;

/**
 * USAGE: php yii load-query/run -f \\weebz\\yii2basics\\migrations\\m241010_171508_always_run_migration
 * 
 */
class LoadQueryController extends Controller
{
    public $file_name;
    
    public function options($actionID)
    {
        return ['file_name'];
    }
    
    public function optionAliases()
    {
        return ['f' => 'file_name'];
    }

    public function actionRun()
    {
        $sql = file_get_contents("{$this->file_name}");
        try {
            $result = \Yii::$app->db->createCommand($sql)->execute();
            echo "Cleared table: {$table->name}\n";
        } catch (\yii\db\Exception $e) {
            echo "Error clearing table {$table->name}: " . $e->getMessage() . "\n";
        }
        echo "Query completed. Result: {$result}\n";
    }
}
