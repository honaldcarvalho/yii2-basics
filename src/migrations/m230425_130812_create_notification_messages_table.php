<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notifications}}`.
 */
class m230425_130812_create_notification_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification_messages}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer(),
            'description' => $this->string()->notNull(),
            'type' => "ENUM('success','warning','danger','default','info')",
            'message' => 'LONGTEXT',
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),
            'status' => $this->integer()->defaultValue(1),
        ]);

        $this->addForeignKey(
            'fk-notification_messages-group_id',
            'notification_messages',
            'group_id',
            'groups',
            'id',
            'RESTRICT'
        );

        $this->insert('menus', [
            'id'=> 16,
            'menu_id' => 1,
            'label'   => 'Notifications',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-comment-alt',
            'visible' => 'notification;index',
            'url'     => '#',
            'path'  => 'weebz/controllers',
            'active'  => 'notification',
            'order'   => 5,
            'status'  => true
        ]);

        $this->insert('menus', [
            'id'=> 161,
            'menu_id' => 16,
            'label'   => 'Mesages',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-comment-dots',
            'visible' => 'notification-message;index',
            'url'     => '/notification-message/index',
            'path'  => 'weebz/controllers',
            'active'  => 'notification-message',
            'order'   => 0,
            'status'  => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%notification_messages}}');
    }
}
