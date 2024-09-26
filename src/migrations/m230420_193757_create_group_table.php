<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group}}`.
 */
class m230420_193757_create_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%groups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'status' => $this->boolean()->defaultValue(true)
        ]);

        $this->insert('groups', [
            'id'=>1,
            'name' => '*',
            'status'=>true
        ]);

        $this->insert('groups', [
            'id'=>2,
            'name' => 'Adminstrators',
            'status'=>true
        ]);

        $this->insert('menus', [
            'id'=> 121,
            'menu_id' => 12,
            'label'   => 'Groups',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-users',
            'visible' => 'group;index',
            'url'     => '/group/index',
            'path'  => 'weebz/controllers',
            'active'  => 'group',
            'order'   => 0,
            'status'  => true
        ]);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%groups}}');
    }
}
