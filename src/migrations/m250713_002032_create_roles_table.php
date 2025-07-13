<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%roles}}`.
 */
class m250713_002032_create_roles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%roles}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'group_id' => $this->integer(),
            'controller' => $this->text()->notNull(), // FQCN com namespace
            'actions' => $this->text(),
            'origin' => $this->text()->notNull()->defaultValue('*'),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'status' => $this->integer()->defaultValue(1)
        ]);

        $this->addForeignKey(
            'fk-roles-user_id',
            'roles',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-roles-group_id',
            'roles',
            'group_id',
            'groups',
            'id',
            'CASCADE'
        );

        // Adiciona ao menu (ajuste o menu_id conforme necessário)
        $this->insert('menus', [
            'id'=> 124,
            'menu_id' => 12,
            'label'   => 'Roles',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-shield-halved',
            'visible' => 'role;index',
            'url'     => '/role/index',
            'path'    => 'weebz/controllers',
            'active'  => 'role',
            'order'   => 3,
            'status'  => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%roles}}');
    }
}
