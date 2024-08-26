<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%source_message}}`.
 */
class m230420_193757_create_sourcemessage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = file_get_contents(__DIR__ . '/query_source_messages_insert.sql');
        $this->createTable('{{%source_message}}', [
            'id' => $this->primaryKey(),
            'category' => $this->string()->defaultValue('*'),
            'message' => $this->text(),
        ]);
        $this->execute($sql);

        $this->insert('menus', [
            'id'=> 142,
            'menu_id' => 14,
            'label'   => 'Source Messages',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-comment',
            'visible' => 'source-message;index',
            'url'     => '/source-message/index',
            'path'  => 'weebz/controllers',
            'active'  => 'source-message',
            'order'   => 2,
            'status'  => true
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%source_message}}');
    }
}
