<?php

namespace weebz\yii2basics\models;

use Exception;
use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\models\ModelCommon;
use Yii;

/**
 * This is the model class for table "youtube".
 *
 * @property string $id
 * @property string $group_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $thumbnail
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $status
 * 
 * @property Group $group
 */
class YoutubeMedia extends ModelCommon
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'youtube';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($this->getAttributes() as $key => $value) {
            $scenarios[self::SCENARIO_DEFAULT][] = $key;
            $scenarios[self::SCENARIO_SEARCH][] = $key;
        }
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required', 'on' => self::SCENARIO_DEFAULT],
            [['status'], 'integer'],
            [['id'], 'string', 'max' => 50],
            [['title', 'thumbnail', 'description'], 'string', 'max' => 255],
            [['id'], 'unique'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Video ID',
            'title' => 'Title',
            'thumbnail' => 'Thumbnail',
            'status' => 'Status',
        ];
    }

    static function old_get_channel_videos($log = true, $group_id = null)
    {

        $results = [];

        if ($group_id === null)
            $group_id = AuthController::userGroup();

        $videos = [];
        $channelId = Parameter::findOne(['name' => 'youtube_channelId'])->value;
        $key = Parameter::findOne(['name' => 'youtube_key'])->value;

        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channelId}&maxResults=15&order=date&key={$key}";

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $result = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
        } catch (Exception $ex) {
            $result = [];
        }

        if ($result !== false && !empty($result)) {

            $data  = json_decode($result);
            $group_id = AuthController::userGroup();
            foreach ($data->items as $item) {
                $existingMedia = YoutubeMedia::findOne(['id' => $item->id->videoId]);
                if (!$existingMedia) {
                    if ($item->id->kind === 'youtube#video') {
                        $video_id  = $item->id->videoId;
                        $title     = isset($item->snippet->title) ?  ControllerCommon::stripEmojis($item->snippet->title) : '';
                        $description     = isset($item->snippet->description) ?  ControllerCommon::stripEmojis($item->snippet->description) : '';
                        $publishedAt     = $item->snippet->publishedAt;
                        $thumbnail = '';

                        if (isset($item->snippet->thumbnails->maxres)) {
                            $thumbnail = $item->snippet->thumbnails->maxres->url;
                        } elseif (isset($item->snippet->thumbnails->standard)) {
                            $thumbnail = $item->snippet->thumbnails->standard->url;
                            //} elseif ( isset( $item->snippet->thumbnails->high ) ) {
                            //    $thumbnail = $item->snippet->thumbnails->high->url;
                        } elseif (isset($item->snippet->thumbnails->medium)) {
                            $thumbnail = $item->snippet->thumbnails->medium->url;
                        }

                        $videos[] = [
                            'id'   => $video_id,
                            'group_id'   => $group_id,
                            'title'     => $title,
                            'thumbnail' => $thumbnail,
                            'description' => $description,
                            'created_at' => date('Y-m-d H:i:s', strtotime($publishedAt)),
                            $result = Yii::$app->db->createCommand()->upsert(
                                'youtube',
                                [
                                    'id'   => $video_id,
                                    'title'     => $title,
                                    'thumbnail' => $thumbnail,
                                    'description' => $description,
                                    'created_at' => date('Y-m-d H:i:s', strtotime($publishedAt)),
                                ]
                            )->execute()
                        ];
                        if ($result) {
                            echo "Media {$item->id->videoId} added.\n";
                        } else {
                            echo "Erro on add Media {$item->id->videoId}.\n";
                        }
                    }
                } else {
                    $videos[$item->id->videoId] = "Media {$item->id->videoId} already exists.\n";
                    echo "Media {$item->id->videoId} already exists.\n";
                }
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $videos;
    }

    static function get_channel_videos($log = true, $group_id = null)
    {
        // Enable verbose error reporting for CLI debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Disable JSON formatting to prevent conflict with echo output
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        try {
            $results = [];

            if ($group_id === null)
                $group_id = AuthController::userGroup();

            $videos = [];
            $channelId = Parameter::findOne(['name' => 'youtube_channelId'])->value;
            $key = Parameter::findOne(['name' => 'youtube_key'])->value;

            if (empty($channelId) || empty($key)) {
                echo "Error: Missing Channel ID or Key parameters.\n";
                return [];
            }

            // Convert Channel ID to Uploads Playlist ID
            $playlistId = 'UU' . substr($channelId, 2);

            $playlistUrl = "https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&playlistId={$playlistId}&maxResults=150&key={$key}";

            $playlistData = null;

            // Inner try-catch for playlist fetching
            try {
                $ch = curl_init($playlistUrl);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $playlistResult = curl_exec($ch);
                if (!curl_errno($ch)) {
                    $playlistData = json_decode($playlistResult);
                } else {
                    echo "Curl Error (Playlist): " . curl_error($ch) . "\n";
                }
                curl_close($ch);
            } catch (\Throwable $ex) {
                echo "Error fetching playlist: " . $ex->getMessage() . "\n";
                $playlistData = null;
            }

            if (isset($playlistData->items) && !empty($playlistData->items)) {

                $videoIds = [];
                foreach ($playlistData->items as $item) {
                    $videoIds[] = $item->contentDetails->videoId;
                }
                $idsString = implode(',', $videoIds);

                $videosUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id={$idsString}&key={$key}";

                $ch = curl_init($videosUrl);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $videosResult = curl_exec($ch);

                if (!curl_errno($ch)) {
                    $videosData = json_decode($videosResult);

                    if (isset($videosData->items)) {
                        foreach ($videosData->items as $item) {
                            try {
                                // Duration check (Shorts filter)
                                $durationIso = $item->contentDetails->duration;
                                try {
                                    $interval = new \DateInterval($durationIso);
                                    $seconds = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
                                    if ($seconds <= 30) {
                                        echo "Skipping Short: {$item->id} ({$seconds}s)\n";
                                        continue;
                                    }
                                } catch (\Exception $e) {
                                    // Ignore date parsing errors
                                }

                                $existingMedia = YoutubeMedia::findOne(['id' => $item->id]);

                                if (!$existingMedia) {
                                    $video_id  = $item->id;
                                    echo "Processing new video: {$video_id}...\n";

                                    $rawTitle = isset($item->snippet->title) ? $item->snippet->title : '';
                                    $rawDesc = isset($item->snippet->description) ? $item->snippet->description : '';

                                    if (!function_exists('mb_convert_encoding')) {
                                        throw new \Exception("PHP extension 'mbstring' is missing.");
                                    }

                                    $rawTitle = mb_convert_encoding($rawTitle, 'UTF-8', 'UTF-8');
                                    $rawDesc = mb_convert_encoding($rawDesc, 'UTF-8', 'UTF-8');

                                    // Sanitize special characters
                                    $title = preg_replace('/[\x00-\x1F\x7F]|[\x{10000}-\x{10FFFF}]/u', '', $rawTitle);
                                    $description = preg_replace('/[\x00-\x1F\x7F]|[\x{10000}-\x{10FFFF}]/u', '', $rawDesc);

                                    $title = mb_substr($title, 0, 250);
                                    $description = mb_substr($description, 0, 5000);

                                    $publishedAt = $item->snippet->publishedAt;

                                    $thumbnail = '';
                                    if (isset($item->snippet->thumbnails->maxres)) {
                                        $thumbnail = $item->snippet->thumbnails->maxres->url;
                                    } elseif (isset($item->snippet->thumbnails->standard)) {
                                        $thumbnail = $item->snippet->thumbnails->standard->url;
                                    } elseif (isset($item->snippet->thumbnails->medium)) {
                                        $thumbnail = $item->snippet->thumbnails->medium->url;
                                    }

                                    $result = Yii::$app->db->createCommand()->upsert('youtube', [
                                        'id'   => $video_id,
                                        'group_id' => $group_id,
                                        'title'     => $title,
                                        'thumbnail' => $thumbnail,
                                        'description' => $description,
                                        'created_at' => date('Y-m-d H:i:s', strtotime($publishedAt)),
                                    ])->execute();

                                    if ($result) {
                                        echo "Media {$video_id} added.\n";
                                    } else {
                                        echo "Error saving Media {$video_id}.\n";
                                    }

                                    // Flush buffer to send output to curl immediately
                                    if (ob_get_length()) ob_flush();
                                    flush();
                                } else {
                                    echo "Media {$item->id} already exists.\n";
                                }
                            } catch (\Throwable $e) {
                                echo "\n[ITEM ERROR] Video ID: " . ($item->id ?? 'unknown') . "\n";
                                echo "Message: " . $e->getMessage() . "\n";
                                // Continue loop even if one fails
                            }
                        }
                    }
                } else {
                    echo "Curl Error (Videos): " . curl_error($ch) . "\n";
                }
                curl_close($ch);
            }
            echo "\nExecução finalizada com sucesso.\n";
            exit();
            return $videos;
        } catch (\Throwable $globalEx) {
            // This catches the specific error that was causing "Internal Server Error"
            if (ob_get_level()) ob_end_clean();
            echo "\n\n================ FATAL ERROR ================\n";
            echo "Message: " . $globalEx->getMessage() . "\n";
            echo "File: " . $globalEx->getFile() . " Line: " . $globalEx->getLine() . "\n";
            echo "Trace:\n" . $globalEx->getTraceAsString() . "\n";
            echo "=============================================\n";
            die();
        }
    }
}
