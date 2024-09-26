<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menus}}`.
 */
class m230911_192841_create_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */

    public function safeUp()
    {
        //$sql = file_get_contents(__DIR__ . '/query_menus_insert.sql');

        $this->createTable('{{%params}}', [
            'id' => $this->primaryKey(),
            'configuration_id' => $this->integer()->notNull(),
            'description' => $this->string(),
            'name' => $this->string()->notNull(),
            'value' => $this->text()->notNull(),
            'status' => $this->boolean()->defaultValue(true)
        ]);

        $this->insert('menus', [
            'id'=> 17,
            'menu_id' => 1,
            'label'   => 'System Parameters',
            'icon_style'=> 'fas',
            'icon'    => 'fas fas fa-sliders-h',
            'visible' => 'params;index',
            'url'     => '/params/index',
            'path'  => 'weebz/controllers',
            'active'  => 'params',
            'order'   => 2,
            'status'  => true
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%menus}}');
    }
}
