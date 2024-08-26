<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menus}}`.
 */
class m230420_193739_create_menus_table extends Migration
{
    /**
     * {@inheritdoc}
     */

    public function safeUp()
    {
        //$sql = file_get_contents(__DIR__ . '/query_menus_insert.sql');

        $this->createTable('{{%menus}}', [
            'id' => this->integer()->notNull(),
            'menu_id' => $this->integer(),
            'label' => $this->string(60)->notNull(),
            'icon_style'=> $this->string(3)->defaultValue('fas'),
            'icon' => $this->string(60),
            'visible' => $this->string(60),
            'url' => $this->string(),
            'active'=> $this->string(60),
            'path'=> $this->string()->defaultValue('app'),
            'order' => $this->integer()->defaultValue(0),
            'only_admin' => $this->integer()->defaultValue(0),
            'status' => $this->integer()->defaultValue(0),
        ]);

        $this->addPrimaryKey('PRIMARY', 'menus', 'id');

        $this->addForeignKey(
            'fk-menus-menu_id',
            'menus',
            'menu_id',
            'menus',
            'id',
            'RESTRICT'
        );

        $this->insert('menus', [
            'id'=> 1,
            'menu_id' => null,
            'label'   => 'Configurations',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-cogs',
            'visible' => '',
            'url'     => '#',
            'path'  => 'weebz/controllers',
            'active'  => '',
            'order'   => 1000,
            'status'  => true
        ]);

        $this->insert('menus', [
            'id'=> 11,
            'menu_id' => 1,
            'label'   => 'Menus',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-bars',
            'visible' => 'menu;index',
            'url'     => '/menu/index',
            'path'  => 'weebz/controllers',
            'active'  => 'menu',
            'order'   => 0,
            'status'  => true
        ]);
        
        $this->insert('menus', [
            'id'=> 12,
            'menu_id' => 1,
            'label'   => 'Authentication',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-key',
            'visible' => '',
            'url'     => '#',
            'path'  => 'weebz/controllers',
            'active'  => '',
            'order'   => 1,
            'status'  => true
        ]);

        $this->insert('menus', [
            'id'=> 13,
            'menu_id' => 1,
            'label'   => 'Storage',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-hdd',
            'visible' => '',
            'url'     => '#',
            'path'  => 'weebz/controllers',
            'active'  => '',
            'order'   => 1,
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
