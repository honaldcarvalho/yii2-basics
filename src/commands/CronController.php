<?php 

namespace app\commands;

use weebz\yii2basics\models\InstagramMedia;
use weebz\yii2basics\models\YoutubeMedia;
use yii\console\Controller;

class CronController extends Controller
{
    public $group_id;

    public function options($actionID)
    {
        return ['group_id'];
    }

    public function optionAliases()
    {
        return ['gi' => 'group_id'];
    }

    public function actionInstagramMedia()
    {
        InstagramMedia::saveMediaToDatabase(true,$this->group_id);
    }

    public function actionInstagramToken()
    {
        InstagramMedia::refreshToken();
    }

    public function actionYoutube()
    {
        YoutubeMedia::get_channel_videos();
    }
}
