<?php 

namespace app\commands;

use yii\console\Controller;

/**
 * USAGE: php yii always-run/run -cn \\weebz\\yii2basics\\migrations\\m241010_171508_always_run_migration
 */
class AlwaysRunController extends Controller
{
    public $class_name;
    
    public function options($actionID)
    {
        return ['class_name'];
    }
    
    public function optionAliases()
    {
        return ['cn' => 'class_name'];
    }

    public function actionRun()
    {
        echo "{$this->class_name}, running custom always-run migration...\n";
        $model =  new $this->class_name();
        // Manually trigger the migration
        $migration = new $model();
        $migration->safeUp();

        echo "Always-run migration completed!\n";
    }
}
