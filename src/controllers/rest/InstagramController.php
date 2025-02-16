<?php

namespace weebz\yii2basics\controllers\rest;
use weebz\yii2basics\controllers\rest\AuthController;
use weebz\yii2basics\models\InstagramMedia;
use weebz\yii2basics\models\Parameter;

/**
 * 
 Example of setting up a cron job:
 0 0 * * * /usr/bin/php /path/to/your/instagram_media_fetch.php

 */
class InstagramController extends AuthController {

     public function actionRefreshToken(){
        return InstagramMedia::refreshToken();
    }

    public function actionLoadMedias(){
        return InstagramMedia::saveMediaToDatabase(true);
    }

    public function actionListMedias(){
        return InstagramMedia::find()->all();
    }
    
    public function actionFetchMedias(){
        return InstagramMedia::fetchInstagramMedia(true);
    }

}