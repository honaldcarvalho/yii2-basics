<?php

use yii\db\Migration;

/**
 * Class m230000_000000_alter_actions_column_in_rules_table
 */
class m231229_161615_alter_actions_column_in_rules_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('rules', 'actions', $this->text());
    }

    public function safeDown()
    {
        // Reverte para VARCHAR(255) se necessário
        $this->alterColumn('rules', 'actions', $this->string(255));
    }
}
