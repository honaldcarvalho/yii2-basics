<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%instagram_media}}`.
 */
class m241009_213828_create_instagram_media_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create the table with a specific collation
        $this->createTable('{{%instagram_media}}', [
            'id' => $this->bigInteger()->notNull(),
            'group_id' => $this->integer(),
            'caption' => $this->text(),
            'media_type' => $this->string(20),
            'media_url' => $this->text(),
            'thumbnail_url' => $this->text(),
            'permalink' => $this->text(),
            'timestamp' => $this->dateTime(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci ENGINE=InnoDB');
        $this->addPrimaryKey('pk_instagram_media_id', '{{%instagram_media}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the table if we rollback the migration
        $this->dropTable('{{%instagram_media}}');
    }
}