<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%folders}}`.
 */
class m230420_193740_create_folders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%folders}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'external' => $this->integer()->defaultValue(1),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),
            'status' => $this->integer()->defaultValue(1),
        ]);
        
        $this->insert('folders', [
            'id' => 1,
            'name' => 'common',
            'description' => 'Common',
            'status'=>true
        ]);

        $this->insert('folders', [
            'id' => 2,
            'name' => 'images',
            'description' => 'Images',
            'status'=>true
        ]);

        $this->insert('folders', [
            'id' => 3,
            'name' => 'videos',
            'description' => 'Videos',
            'status'=>true
        ]);

        $this->insert('folders', [
            'id' => 4,
            'name' => 'documents',
            'description' => 'Documents',
            'status'=>true
        ]);

        $this->insert('menus', [
            'id'=> 6,
            'menu_id' => 2,
            'label'   => 'Folders',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-folder',
            'visible' => 'folder;index',
            'url'     => '/folder/index',
            'path'  => 'weebz/controllers',
            'active'  => 'folder',
            'order'   => 0,
            'status'  => true
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%folders}}');
    }
}
