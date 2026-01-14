<?php

namespace weebz\yii2basics\controllers\rest;
use weebz\yii2basics\controllers\rest\AuthController;
use weebz\yii2basics\models\InstagramMedia;
use weebz\yii2basics\models\Parameter;
use weebz\yii2basics\models\YoutubeMedia;
use Yii;

/**
 * 
 Example of setting up a cron job:
 0 0 * * * /usr/bin/php /path/to/your/instagram_media_fetch.php

 */
class CronController extends \yii\rest\Controller {

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function init(): void
    {   
        parent::init();
        Yii::$app->user->enableSession = false;
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $body = Yii::$app->request->getBodyParams();
        $parameter = Parameter::findOne(['name'=>'cron_token']);
        if(!isset($body['k']) || $body['k'] != $parameter->value){
            throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'Forbidden.'));
        }
        return $behaviors;
    }

     public function actionRefreshToken(){
        return InstagramMedia::refreshToken();
    }

    public function actionIload(){
        return InstagramMedia::saveMediaToDatabase(true,2);
    }

    public function actionYload(){
        try {
            $data = YoutubeMedia::get_channel_videos(true,2);
        } catch (\Throwable $th) {
            print_r($th);
        }
        return $data;
    }
}