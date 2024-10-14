<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\controllers\ControllerCommon;

/**
 * This is the model class for table "instagram_media".
 *
 * @property int $id
 * @property int $group_id
 * @property string|null $caption
 * @property string $media_type
 * @property string $media_url
 * @property string|null $thumbnail_url
 * @property string $permalink
 * @property string $timestamp
 * @property string|null $created_at
 * 
 * @property Group $group
 */
class InstagramMedia extends ModelCommon
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'instagram_media';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'media_type', 'media_url', 'permalink', 'timestamp'], 'required','on'=>self::SCENARIO_DEFAULT],
            [['id'], 'integer'],
            [['caption', 'media_url', 'thumbnail_url', 'permalink'], 'string'],
            [['timestamp', 'created_at'], 'safe'],
            [['media_type'], 'string', 'max' => 20],
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
            'id' => 'ID',
            'caption' => 'Caption',
            'media_type' => 'Media Type',
            'media_url' => 'Media Url',
            'thumbnail_url' => 'Thumbnail Url',
            'permalink' => 'Permalink',
            'timestamp' => 'Timestamp',
            'created_at' => 'Created At',
        ];
    }

    public static function getToken(){
        $shortLivedAccessToken = 'IGQWRNbk1kUzRhRnNHRnVtTF9ET3FsZAUdqMWFfT3RZAQUhkNmdzaHFIQ19FUWpOSFY0UVF0VFVUaVY1WU0tQ19HRlZAIZAW9uNnVnQzJMMHdaY19CZAktMU1M4ckRZASXd0X3hhRDFVdEg0NkM4TzVOMnZA0ZAGZAGOVNWZAlkZD';

        $exchangeUrl = 'https://graph.instagram.com/access_token?' . http_build_query([
            'grant_type' => 'ig_exchange_token',
            'client_secret' => '39e3a3f073cf3283367a2d71913f7f7e', // Found in your Facebook App settings
            'access_token' => $shortLivedAccessToken,
        ]);
        
        $response = file_get_contents($exchangeUrl);
        $tokenData = json_decode($response, true);
        
        $longLivedAccessToken = $tokenData['access_token'];
        $expiresIn = $tokenData['expires_in']; // Seconds (60 days)
    }

    static public function refreshToken(){
        $parameter = Parameter::findOne(['name'=>'instagram_token']);
        
        $longLivedAccessToken = $parameter->value;
        $refreshUrl = 'https://graph.instagram.com/refresh_access_token?' . http_build_query([
            'grant_type' => 'ig_refresh_token',
            'access_token' => $longLivedAccessToken,
        ]);
        
        $response = file_get_contents($refreshUrl);
        $refreshedTokenData = json_decode($response, true);
        
        $refreshedLongLivedAccessToken = $refreshedTokenData['access_token'];
        $expiresIn = $refreshedTokenData['expires_in']; // New expiration time (60 days)

        $parameter->value = $refreshedLongLivedAccessToken;
        $parameter->save();
        
        return [
            'refreshedLongLivedAccessToken'=>$refreshedLongLivedAccessToken,
            'refreshedTokenData'=>$refreshedTokenData,
            'refreshedTokenData'=>$expiresIn,
        ];
    }

    static function fetchInstagramMedia() {

        $instagram_token = Parameter::findOne(['name'=>'instagram_token']);
        $instagram_userid = Parameter::findOne(['name'=>'instagram_userid']);
        $response = [];
        if($instagram_token !== null && $instagram_userid !== null) {
            $mediaUrl = "https://graph.instagram.com/{$instagram_userid->value}/media?" . http_build_query([
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp',
                'access_token' => $instagram_token->value,
            ]);

            $response = file_get_contents($mediaUrl);
        }
        return json_decode($response, true);

    }

    static function saveMediaToDatabase($log = true,$group_id = null) {
        $results = [];

        if($group_id === null)
            $group_id = AuthController::userGroup();

        $mediaData = self::fetchInstagramMedia();
        foreach ($mediaData['data'] as $media) {
            // Check if the media already exists in the database
            $existingMedia = InstagramMedia::findOne($media['id']);
            if (!$existingMedia) {
                // Create a new InstagramMedia record
                $newMedia = new InstagramMedia();
                $newMedia->id = $media['id'];
                $newMedia->group_id = $group_id;
                $newMedia->caption = isset($media['caption']) ?  ControllerCommon::stripEmojis($media['caption']) : '';
                $newMedia->media_type = $media['media_type'];
                $newMedia->media_url = $media['media_url'];
                $newMedia->thumbnail_url = isset($media['thumbnail_url']) ? $media['thumbnail_url'] : null;
                $newMedia->permalink = $media['permalink'];
                $newMedia->timestamp = date('Y-m-d H:i:s', strtotime($media['timestamp']));
    
                // Save to database
                if ($newMedia->save()) {
                    if(!$log)
                        $results[] = "Media {$media['id']} saved successfully.";
                    else 
                        echo "Media {$media['id']} saved successfully.\n";
                } else {
                    if(!$log)
                        $results[] = "Failed to save media {$media['id']}.";
                    else 
                        echo "Failed to save media {$media['id']}.\n";
                }
            } else {
                if(!$log)
                    $results[] = "Media {$media['id']} already exists.\n";
                else 
                    echo "Media {$media['id']} already exists.\n";
            }
        }

        if(!$log)
            return $results;
    }
}