<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%licenses}}`.
 */
class m231223_143408_create_licenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%licenses}}', [
            'id' => $this->primaryKey(),
            'license_type_id'=> $this->integer()->notNull(),
            'group_id'=>  $this->integer()->notNull(),
            'validate' => $this->dateTime(),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),
            'status' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk-licenses-license_type',
            'licenses',
            'license_type_id',
            'license_types',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk-licenses-group_id',
            'licenses',
            'group_id',
            'groups',
            'id',
            'CASCADE'
        );

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'license',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('menus', [
            'id'=> 16,
            'menu_id' => 1,
            'label'   => 'Licenses',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-certificate',
            'visible' => 'license;index',
            'url'     => '/license/index',
            'path'  => 'weebz/controllers',
            'active'  => 'license',
            'order'   => 6,
            'status'  => true
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-licenses-group_id',
            'licenses',
        );
        $this->dropTable('{{%licenses}}');
    }
}
