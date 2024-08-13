<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_group}}`.
 */
class m230425_124535_create_user_groups_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_groups}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'group_id' => $this->integer(),
        ]);

        // add foreign keys for table `user_group`
        $this->addForeignKey(
            'fk-user_groups-user_id',
            'user_groups',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk-user_groups-group_id',
            'user_groups',
            'group_id',
            'groups',
            'id',
            'RESTRICT'
        );

        $this->insert('user_groups', [
            'user_id' => 1,//admin
            'group_id' => 1,//masters
        ]);

        $this->insert('user_groups', [
            'user_id' => 1,//admin
            'group_id' => 2,//administrators
        ]);

    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_group}}');
    }
}
