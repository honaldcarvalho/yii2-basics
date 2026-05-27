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
            'menu_id' => null,
            'icon' => 'language',
            'url' => '/configuration/i18n',
            'order' => 99,
            'status' => 1,
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
