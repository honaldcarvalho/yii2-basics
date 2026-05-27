<?php

namespace app\services;

use Yii;
use yii\db\Query;
use yii\helpers\Json;
use app\models\Configuration;

/**
 * Service to sync translations from the central i18n API.
 */
class TranslationSyncService
{
    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $apiToken;

    public function __construct()
    {
        // In console context Configuration::get() may fail.
        // Fall back to a direct DB query to fetch i18n settings.
        if (Yii::$app instanceof \yii\console\Application) {
            $row = (new \yii\db\Query())
                ->select(['i18n_api_url', 'i18n_api_token'])
                ->from('{{%configurations}}')
                ->limit(1)
                ->one();
            $this->apiUrl   = $row['i18n_api_url']   ?? null;
            $this->apiToken = $row['i18n_api_token'] ?? null;
        } else {
            $config = Configuration::get();
            $this->apiUrl   = $config->i18n_api_url;
            $this->apiToken = $config->i18n_api_token;
        }
    }

    /**
     * Pulls translations from the central API and performs an upsert on local tables.
     * @return array statistics of the process.
     * @throws \Exception
     */
    public function pull(): array
    {
        if (!$this->apiUrl) {
            throw new \Exception('I18N_API_URL refers to an empty value. Cannot sync translations.');
        }

        $endpoint = rtrim($this->apiUrl, '/') . '/api/pull';

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = ['Accept: application/json'];
        if ($this->apiToken) {
            $headers[] = 'Authorization: Bearer ' . $this->apiToken;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Failed to fetch translations from API. HTTP Code: {$httpCode}. Error: {$error}. Response: {$response}");
        }

        $data = Json::decode($response);
        if (!is_array($data)) {
            throw new \Exception("Invalid JSON payload from API.");
        }

        return $this->upsertTranslations($data);
    }

    /**
     * Upserts an array of translations into source_messages and messages.
     * @param array $payload
     * @return array
     */
    public function upsertTranslations(array $payload): array
    {
        $db = Yii::$app->db;
        $stats = ['source_added' => 0, 'translations_upserted' => 0];

        $transaction = $db->beginTransaction();

        try {
            foreach ($payload as $item) {
                if (empty($item['category']) || empty($item['message'])) {
                    continue; // Skip invalid items
                }

                $category = $item['category'];
                $message = $item['message'];

                $sourceId = (new Query())
                    ->select('id')
                    ->from('source_message')
                    ->where(['category' => $category, 'message' => $message])
                    ->scalar();

                if (!$sourceId) {
                    $db->createCommand()->insert('source_message', [
                        'category' => $category,
                        'message'  => $message,
                    ])->execute();
                    $sourceId = $db->getLastInsertID();
                    $stats['source_added']++;
                }

                if (!empty($item['translations']) && is_array($item['translations'])) {
                    foreach ($item['translations'] as $lang => $translation) {
                        $exists = (new Query())
                            ->from('message')
                            ->where(['id' => $sourceId, 'language' => $lang])
                            ->exists();

                        if ($exists) {
                            $db->createCommand()->update('message', [
                                'translation' => $translation
                            ], ['id' => $sourceId, 'language' => $lang])->execute();
                        } else {
                            $db->createCommand()->insert('message', [
                                'id'          => $sourceId,
                                'language'    => $lang,
                                'translation' => $translation,
                            ])->execute();
                        }
                        $stats['translations_upserted']++;
                    }
                }
            }

            $transaction->commit();

            if (Yii::$app->cache) {
                Yii::$app->cache->flush();
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $stats;
    }
}
