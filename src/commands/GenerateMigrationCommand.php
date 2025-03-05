<?php

namespace app\commands;

use yii\console\Controller;
use yii\helpers\Console;
use yii\db\Command;

class GenerateMigrationCommand extends Controller
{
    /**
     * Gera uma migration para inserção de dados de tabelas selecionadas.
     */
    public function actionGenerate($tables = [])
    {
        if (empty($tables)) {
            echo "Por favor, forneça uma lista de tabelas para gerar a migration.\n";
            return;
        }

        // Conectar ao banco de dados e obter os dados das tabelas selecionadas
        $db = \Yii::$app->db;

        foreach ($tables as $table) {
            $this->generateTableMigration($table, $db);
        }
    }

    /**
     * Gera a migration de inserção de dados para uma tabela específica.
     * 
     * @param string $table Nome da tabela
     * @param \yii\db\Connection $db Conexão do banco de dados
     */
    protected function generateTableMigration($table, $db)
    {
        // Verificar se a tabela existe no banco de dados
        if ($db->getTableSchema($table) === null) {
            echo "Tabela {$table} não encontrada no banco de dados.\n";
            return;
        }

        // Obter as colunas da tabela
        $columns = $db->getTableSchema($table)->columns;
        $columnNames = array_keys($columns);

        // Gerar o nome do arquivo da migration
        $migrationName = 'm' . gmdate('ymd_His') . '_insert_data_into_' . $table;
        $migrationFile = \Yii::getAlias('@console/migrations/') . $migrationName . '.php';

        // Gerar o código da migration
        $migrationCode = $this->generateMigrationCode($table, $columnNames);

        // Criar o arquivo da migration
        file_put_contents($migrationFile, $migrationCode);

        echo "Migration gerada com sucesso para a tabela {$table}: {$migrationFile}\n";
    }

    /**
     * Gera o código PHP para a migration de inserção de dados.
     * 
     * @param string $table Nome da tabela
     * @param array $columns Nome das colunas da tabela
     * @return string Código PHP gerado
     */
    protected function generateMigrationCode($table, $columns)
    {
        // Cabeçalho do arquivo
        $code = "<?php\n\n";
        $code .= "use yii\\db\\Migration;\n\n";
        $code .= "class " . ucfirst($table) . "DataMigration extends Migration\n";
        $code .= "{\n";
        $code .= "    public function safeUp()\n";
        $code .= "    {\n";
        $code .= "        \$this->batchInsert('{$table}', [" . implode(", ", array_map(fn($col) => "'$col'", $columns)) . "], [\n";
        
        // Gerar valores de exemplo para inserção
        // Você pode melhorar isso para pegar os dados reais ou fazer algo mais dinâmico
        $code .= "            // Exemplo de dados\n";
        $code .= "            // ['valor1', 'valor2', 'valor3'],\n";
        
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function safeDown()\n";
        $code .= "    {\n";
        $code .= "        \$this->delete('{$table}');\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }
}
