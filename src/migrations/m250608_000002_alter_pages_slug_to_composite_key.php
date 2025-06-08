<?php

use yii\db\Migration;

/**
 * Handles changing the unique slug constraint to a composite key (slug + language_id).
 */
class m250608_000002_alter_pages_slug_to_composite_key extends Migration
{
    public function safeUp()
    {
        // Remover índice único antigo em slug (se existir)
        $this->dropIndex('slug', '{{%pages}}');

        // Criar índice único composto entre slug + language_id
        $this->createIndex(
            'idx-pages-slug-language_id-unique',
            '{{%pages}}',
            ['slug', 'language_id'],
            true // unique
        );
    }

    public function safeDown()
    {
        // Remove índice composto
        $this->dropIndex('idx-pages-slug-language_id-unique', '{{%pages}}');

        // Recria o índice único apenas no slug
        $this->createIndex(
            'slug',
            '{{%pages}}',
            'slug',
            true // unique
        );
    }
}
