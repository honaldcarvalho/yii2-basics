<?php

namespace weebz\yii2basics\controllers\rest;
use weebz\yii2basics\controllers\rest\AuthController;
use weebz\yii2basics\models\YoutubeMedia;

/**
 * 
 Example of setting up a cron job:
 0 0 * * * /usr/bin/php /path/to/your/instagram_media_fetch.php

 */
class YoutubeController extends AuthController {

    public function actionLoadMedias(){
        return "no";
    }

    public function actionListMedias(){
        return YoutubeMedia::find()->all();
    }
    

}
