<?php

namespace weebz\yii2basics\controllers\rest;
use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\models\InstagramMedia;
use weebz\yii2basics\models\Parameter;

/**
 * 
 Example of setting up a cron job:
 0 0 * * * /usr/bin/php /path/to/your/instagram_media_fetch.php

 */
class InstagramController extends AuthController {

    public function actionRefreshToken(){
        $this->refreshToken();
    }

    public function actionLoadMedias(){
        $this->saveMediaToDatabase();
    }

}
