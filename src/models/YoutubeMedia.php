<?php

namespace weebz\yii2basics\models;

use Exception;
use weebz\yii2basics\controllers\AuthController;
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
            [['id'], 'required','on'=>self::SCENARIO_DEFAULT],
            [['status'], 'integer'],
            [['id'], 'string', 'max' => 50],
            [['title', 'thumbnail','description'], 'string', 'max' => 255],
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

    static function get_channel_videos() {

        $videos = [];
        $channelId = Parameter::findOne(['name'=>'youtube_channelId']);
        $key = Parameter::findOne(['name'=>'youtube_key']);

        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channelId}&maxResults=50&order=date&key={$key}";
        
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

        if($result !== false && !empty($result)){

            $data  = json_decode($result);
            $group_id = AuthController::userGroup();
            foreach ( $data->items as $item ) {
                if ( $item->id->kind === 'youtube#video' ) {
                    $video_id  = $item->id->videoId;
                    $title     = $item->snippet->title;
                    $description     = $item->snippet->description;
                    $publishedAt     = $item->snippet->publishedAt;
                    $thumbnail = '';
        
                    if ( isset( $item->snippet->thumbnails->maxres ) ) {
                        $thumbnail = $item->snippet->thumbnails->maxres->url;
                    } elseif ( isset( $item->snippet->thumbnails->standard ) ) {
                        $thumbnail = $item->snippet->thumbnails->standard->url;
                    //} elseif ( isset( $item->snippet->thumbnails->high ) ) {
                    //    $thumbnail = $item->snippet->thumbnails->high->url;
                    } elseif ( isset( $item->snippet->thumbnails->medium ) ) {
                        $thumbnail = $item->snippet->thumbnails->medium->url;
                    }
        
                    $videos[] = [
                        'id'   => $video_id,
                        'group_id'   => $group_id,
                        'title'     => $title,
                        'thumbnail' => $thumbnail,
                        'description' => $description,
                        'created_at' => date('Y-m-d H:i:s', strtotime($publishedAt)),
                        Yii::$app->db->createCommand()->upsert('youtube', 
                        [
                            'id'   => $video_id,
                            'title'     => $title,
                            'thumbnail' => $thumbnail,
                            'description' => $description,
                            'created_at' => date('Y-m-d H:i:s', strtotime($publishedAt)),
                        ]
                        )->execute()
                    ];
        
                }
            }

        }  

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $videos;
    }

}
