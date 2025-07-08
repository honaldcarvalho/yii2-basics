<?php

use yii\db\Migration;

/**
 * Class m231223_004846_add_trigger_groups_after_insert_to_table_groups
 */
class m231223_004846_alter_trigger_groups_after_insert_to_table_groups extends Migration
{
    public function safeUp()
    {
        $this->execute('DROP TRIGGER IF EXISTS `groups_after_insert`;');

        $sql = <<<SQL
        CREATE TRIGGER `groups_after_insert` AFTER INSERT ON `groups`
        FOR EACH ROW
        BEGIN
            DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

            INSERT IGNORE INTO `rules` (`user_id`, `group_id`, `controller`, `actions`, `origin`,`path`, `status`) VALUES 
                (NULL, NEW.id, 'site', 'index;dashboard', '*', 'weebz/controllers', 1),
                (NULL, NEW.id, 'site', 'index;dashboard', '*', 'app', 1),
                (NULL, NEW.id, 'user', 'profile;edit;change-lang;change-theme', '*', 'weebz/controllers', 1),
                (NULL, NEW.id, 'file', 'index;create;view;update;delete;list;upload;move;remove-file;delete-files;send', '*', 'weebz/controllers', 1),
                (NULL, NEW.id, 'folder', 'index;create;view;update;delete', '*', 'weebz/controllers', 1);
        END;
        SQL;

        $this->execute($sql);
    }

    public function safeDown()
    {
        $this->execute('DROP TRIGGER IF EXISTS `groups_after_insert`;');
    }
}