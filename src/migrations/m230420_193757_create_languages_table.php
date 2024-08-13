<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%languages}}`.
 */
class m230420_193757_create_languages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%languages}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(10)->notNull(),
            'name' => $this->string()->notNull(),
            'status' => $this->integer()->defaultValue(0),
        ]);
        
        $this->insert('languages', [
            'code' => 'en-US',
            'name' => 'English (EUA)',
            'status'=> 1
        ]);

        $this->insert('languages', [
            'code' => 'pt-BR',
            'name' => 'Portugues (BR)',
            'status'=> 1
        ]);

        $this->insert('languages', [
            'code' => 'es',
            'name' => 'Español (ES)',
            'status'=> 1
        ]);

        $this->insert('menus', [
            'id'=> 5,
            'menu_id' => 1,
            'label'   => 'Translations',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-globe',
            'visible' => '',
            'url'     => '#',
            'path'  => 'app',
            'active'  => '',
            'order'   => 4,
            'status'  => true
        ]);

        $this->insert('menus', [
            'id'=> 7,
            'menu_id' => 5,
            'label'   => 'Languages',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-language',
            'visible' => 'language;index',
            'url'     => '/language/index',
            'path'  => 'app',
            'active'  => 'language',
            'order'   => 0,
            'status'  => true
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%languages}}');
    }
}
