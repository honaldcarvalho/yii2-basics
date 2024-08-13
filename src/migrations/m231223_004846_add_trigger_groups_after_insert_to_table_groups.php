<?php

use yii\db\Migration;

/**
 * Class m231223_004846_add_trigger_groups_after_insert_to_table_groups
 */
class m231223_004846_add_trigger_groups_after_insert_to_table_groups extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $db_user = env('DB_USER');
        $sql = <<< SQL
            CREATE DEFINER=`$db_user`@`%` TRIGGER `groups_after_insert` after INSERT ON `groups` FOR EACH ROW BEGIN
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `origin`, `status`) VALUES (NULL,NEW.id,  'site', 'index;dashboard-captive', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `origin`, `status`) VALUES (NULL,NEW.id,  'user', 'profile;edit;', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `origin`, `status`) VALUES (NULL,NEW.id,  'upload', 'send;crop;multi', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `origin`, `status`) VALUES (NULL,NEW.id,  'file', 'index;create;view;update;delete;list;upload;move;remove-file;delete-files;send', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `origin`, `status`) VALUES (NULL,NEW.id,  'folder', 'index;create;view;update;delete', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `path`, `origin`, `status`) VALUES (NULL,NEW.id,  'nas', 'index;create;view;update;delete;print', 'app/custom', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `path`, `origin`, `status`) VALUES (NULL,NEW.id,  'radacct', 'index;view;print', 'app/custom', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `path`, `origin`, `status`) VALUES (NULL,NEW.id,  'captive', 'index;view;print', 'app/custom', '*', 1);
                INSERT INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `path`, `origin`, `status`) VALUES (NULL,NEW.id,  'radcheck', 'index;create;view;update;delete;print', 'app/custom', '*', 1);
            END

        SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER `groups_after_insert`;');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231223_004846_add_trigger_groups_after_insert_to_table_groups cannot be reverted.\n";

        return false;
    }
    */
}
