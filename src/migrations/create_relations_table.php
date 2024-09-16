<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%licenses}}`.
 */
class m231223_143408_create_relations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%relations}}', [
            'id' => $this->primaryKey(),
            'a_class'=>  $this->string()->notNull(),
            'b_class'=>  $this->string()->notNull(),
            'a_value' => $this->integer()->notNull(),
            'b_value' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),
            'status' => $this->integer()->notNull()->defaultValue(1),
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'license',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
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
