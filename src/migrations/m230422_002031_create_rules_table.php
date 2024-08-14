<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rules}}`.
 */
class m230422_002031_create_rules_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%rules}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'group_id' => $this->integer(),
            'controller' => $this->string()->notNull(),
            'actions' => $this->string()->notNull(),
            'path' => $this->string()->defaultValue('app'),
            'origin' => $this->text()->notNull()->defaultValue('*'),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'status' => $this->integer()->defaultValue(1)
        ]);

        $this->addForeignKey(
            'fk-ru-user_id',
            'rules',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-rg-group_id',
            'rules',
            'group_id',
            'groups',
            'id',
            'CASCADE'
        );

        $this->insert('menus', [
            'id'=> 11,
            'menu_id' => 2,
            'label'   => 'Rules',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-person-booth',
            'visible' => 'rule;index',
            'url'     => '/rule/index',
            'path'  => 'weebz/controllers',
            'active'  => 'rule',
            'order'   => 2,
            'status'  => true
        ]);
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%rules}}');
    }
}
