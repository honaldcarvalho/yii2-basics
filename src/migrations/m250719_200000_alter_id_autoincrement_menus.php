<?php

use yii\db\Migration;

/**
 * Corrige o campo `id` da tabela `menus` para ser auto incremento.
 */
class m250719_200000_alter_id_autoincrement_menus extends Migration
{
    public function safeUp()
    {
        // Altera diretamente sem dropar PK
        $this->alterColumn('menus', 'id', $this->integer()->notNull()->append('AUTO_INCREMENT'));
    }

    public function safeDown()
    {
        // Reverte para inteiro comum (sem auto_increment)
        $this->alterColumn('menus', 'id', $this->integer()->notNull());
    }
}
