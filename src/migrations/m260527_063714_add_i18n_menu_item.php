<?php

use yii\db\Migration;

/**
 * Class m260527_063714_add_i18n_menu_item
 */
class m260527_063714_add_i18n_menu_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%menus}}', [
            'label' => 'i18n',
            'menu_id' => 1, // System Menu
            'icon' => 'language',
            'icon_style' => 'fas',
            'url' => '/configuration/i18n',
            'visible' => 'configuration;i18n',
            'active' => 'configuration',
            'path' => 'app/controllers',
            'order' => 99,
            'status' => 1,
            'only_admin' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%menus}}', ['label' => 'i18n']);
    }
}
