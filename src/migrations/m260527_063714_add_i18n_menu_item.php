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
            'name' => 'i18n',
            'id_parent' => null,
            'description' => 'i18n Configurations',
            'icon' => 'language',
            'url' => '/configuration/i18n',
            'id_order' => 99,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1,
            'is_deleted' => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%menus}}', ['name' => 'i18n']);
    }
}
