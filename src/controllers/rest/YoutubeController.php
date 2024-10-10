<?php

namespace weebz\yii2basics\controllers\rest;
use weebz\yii2basics\controllers\rest\AuthController;
use weebz\yii2basics\models\YoutubeMedia;
use weebz\yii2basics\models\Parameter;

/**
 * 
 Example of setting up a cron job:
 0 0 * * * /usr/bin/php /path/to/your/instagram_media_fetch.php

 */
class InstagramController extends AuthController {

    public function actionLoadMedias(){
        return YoutubeMedia::get_channel_videos(false);
    }

    public function actionListMedias(){
        return YoutubeMedia::find()->all();
    }
    

}
