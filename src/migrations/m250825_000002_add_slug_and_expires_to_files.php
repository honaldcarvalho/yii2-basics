<?php

use yii\db\Migration;

/**
 * Handles adding columns `slug` and `expires_at` to table `files`.
 */
class m250825_000002_add_slug_and_expires_to_files extends Migration
{
    public function safeUp()
    {
        // adiciona a coluna slug (32 caracteres é um bom tamanho)
        $this->addColumn('{{%files}}', 'slug', $this->string(32)->null()->unique()->after('id'));
        // adiciona expires_at (timestamp UNIX)
        $this->addColumn('{{%files}}', 'expires_at', $this->integer()->null()->after('slug'));

        // popula registros existentes
        $rows = (new \yii\db\Query())->from('{{%files}}')->select(['id'])->all();
        foreach ($rows as $row) {
            $slug = Yii::$app->security->generateRandomString(32);
            $expires = time() + 86400; // 1 dia (24h * 60m * 60s)
            $this->update('{{%files}}', [
                'slug'       => $slug,
                'expires_at' => $expires,
            ], ['id' => $row['id']]);
        }
    }

    public function safeDown()
    {
        $this->dropColumn('{{%files}}', 'expires_at');
        $this->dropColumn('{{%files}}', 'slug');
    }
}
