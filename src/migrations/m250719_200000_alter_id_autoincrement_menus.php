<?php

use yii\db\Migration;

/**
 * Corrige o campo `id` da tabela `menus` para ser auto incremento.
 */
class m250719_200000_alter_id_autoincrement_menus extends Migration
{
    public function safeUp()
    {
        // Remove PK antiga se necessário
        $this->dropPrimaryKey('PRIMARY', 'menus');

        // Altera o campo `id` para AUTO_INCREMENT
        $this->alterColumn('menus', 'id', $this->primaryKey()->unsigned()->notNull());

        // Recria a PK se necessário
        $this->addPrimaryKey('PRIMARY', 'menus', 'id');
    }

    public function safeDown()
    {
        // Reverte para inteiro simples
        $this->alterColumn('menus', 'id', $this->integer()->notNull());

        // Garante que a PK permaneça
        $this->addPrimaryKey('PRIMARY', 'menus', 'id');
    }
}
