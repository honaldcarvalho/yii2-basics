<?php

use app\models\Menu;
use yii\db\Migration;


class m240807_161428_add_debug_to_menus_table extends Migration
{
    /**
     * {@inheritdoc}
     */

    public function safeUp()
    {
        $count = Menu::find()->select('id')->orderBy(['id'=>SORT_DESC])->one();
        $id = $count->id;
        $this->insert('menus', [
            'id'=> $id + 1,
            'menu_id' => null,
            'label'   => 'Development',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-file-code',
            'visible' => '',
            'url'     => '#',
            'path'  => 'app',
            'active'  => '',
            'order'   => 2000,
            'only_admin'   => 1,
            'status'  => true
        ]);

        $this->insert('menus', [
            'id'=> $id + 3,
            'menu_id' => $id + 1,
            'label'   => 'Gii',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-file-code',
            'visible' => '',
            'url'     => '/gii',
            'path'  => 'app',
            'active'  => 'gii',
            'order'   => 1,
            'only_admin'   => 1,
            'status'  => true
        ]);

        $this->insert('menus', [
            'id'=> $id + 2,
            'menu_id' => $id + 1,
            'label'   => 'Debug',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-file-code',
            'visible' => '',
            'url'     => 'debug/default/view',
            'path'  => 'app',
            'active'  => '',
            'order'   => 0,
            'only_admin'   => 1,
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