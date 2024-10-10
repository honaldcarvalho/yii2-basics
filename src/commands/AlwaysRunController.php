<?php 

namespace weebz\yii2basics\commands;

use m241010_171508_always_run_migration;
use yii\console\Controller;

class AlwaysRunController extends Controller
{
    public function actionRun()
    {
        echo "Running custom always-run migration...\n";

        // Manually trigger the migration
        $migration = new m241010_171508_always_run_migration();
        $migration->safeUp();

        echo "Always-run migration completed!\n";
    }
}
