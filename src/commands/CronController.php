<?php 

namespace weebz\yii2basics\commands;

use weebz\yii2basics\models\InstagramMedia;
use weebz\yii2basics\models\YoutubeMedia;
use yii\console\Controller;

/**
 * RUN
 * 
 php yii cron/itoken -gi=2
 php yii cron/iload -gi=2
 php yii cron/yload -gi=2
 *
 */

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

    public function actionIload()
    {
        InstagramMedia::saveMediaToDatabase(true,$this->group_id);
    }

    public function actionItoken()
    {
        InstagramMedia::refreshToken();
    }

    public function actionYload()
    {
        YoutubeMedia::get_channel_videos(true,$this->group_id);
    }
}
