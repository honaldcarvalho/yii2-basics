<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menus}}`.
 */
class m230911_192841_create_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */

    public function safeUp()
    {
        //$sql = file_get_contents(__DIR__ . '/query_menus_insert.sql');

        $this->createTable('{{%params}}', [
            'id' => $this->primaryKey(),
            'configuration_id' => $this->integer()->notNull(),
            'description' => $this->string(),
            'name' => $this->string()->notNull(),
            'value' => $this->text()->notNull(),
            'status' => $this->boolean()->defaultValue(true)
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
