<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\services\TranslationSyncService;

/**
 * Console controller to sync i18n translations from the central API.
 */
class TranslationSyncController extends Controller
{
    /**
     * Pulls the latest translations from the central API and upserts them into the database.
     * 
     * Command: `php yii translation-sync/pull`
     * 
     * @return int Exit code
     */
    public function actionPull()
    {
        $this->stdout("Starting translation synchronization...\n\n");

        $service = new TranslationSyncService();

        try {
            $stats = $service->pull();
            
            $this->stdout("Synchronization completed successfully!\n");
            $this->stdout("Sources added: {$stats['source_added']}\n");
            $this->stdout("Translations upserted: {$stats['translations_upserted']}\n");
            
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Error during synchronization:\n");
            $this->stderr($e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
