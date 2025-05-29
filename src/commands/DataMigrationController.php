<?php

namespace weebz\yii2basics\commands;

use yii\console\Controller;
use yii\db\Connection;

class DataMigrationController extends Controller
{
    public $tables;

    public function options($actionID)
    {
        return ['tables'];
    }

    public function optionAliases()
    {
        return ['tables' => 'tables'];
    }

    /**
     * Gera uma migration para inserção de dados de tabelas selecionadas.
     */
    public function actionGenerate()
    {
        $tables = explode(',', $this->tables);

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
    protected function generateTableMigration($table, Connection $db)
    {
        // Verificar se a tabela existe no banco de dados
        if ($db->getTableSchema($table) === null) {
            echo "Tabela {$table} não encontrada no banco de dados.\n";
            return;
        }

        // Obter os dados da tabela
        $data = (new \yii\db\Query())
            ->select('*')
            ->from($table)
            ->all($db);

        if (empty($data)) {
            echo "Nenhum dado encontrado para a tabela {$table}.\n";
            return;
        }

        // Obter as colunas da tabela
        $columns = array_keys($db->getTableSchema($table)->columns);

        // Gerar o nome do arquivo da migration
        $migrationName = 'm' . gmdate('ymd_His') . '_insert_data_into_' . $table;
        $migrationFile = \Yii::getAlias('@app/common/migrations/') . $migrationName . '.php';

        // Gerar o código da migration
        $migrationCode = $this->generateMigrationCode($table, $columns, $data);

        // Criar o arquivo da migration
        file_put_contents($migrationFile, $migrationCode);

        echo "Migration gerada com sucesso para a tabela {$table}: {$migrationFile}\n";
    }

    /**
     * Gera o código PHP para a migration de inserção de dados.
     * 
     * @param string $table Nome da tabela
     * @param array $columns Nome das colunas da tabela
     * @param array $data Dados da tabela a serem inseridos
     * @return string Código PHP gerado
     */
    protected function generateMigrationCode($table, $columns, $data)
    {
        // Cabeçalho do arquivo
        $code = "<?php\n\n";
        $code .= "use yii\\db\\Migration;\n\n";
        $code .= "class " . ucfirst($table) . "DataMigration extends Migration\n";
        $code .= "{\n";
        $code .= "    public function safeUp()\n";
        $code .= "    {\n";
        $code .= "        \$this->batchInsert('{$table}', [" . implode(", ", array_map(fn($col) => "'$col'", $columns)) . "], [\n";

        // Gerar os dados para inserção
        foreach ($data as $row) {
            $values = array_map(function ($value) {
                // Verifica se o valor é nulo ou precisa ser escapado
                return is_null($value) ? 'null' : "'" . addslashes($value) . "'";
            }, array_values($row));
            $code .= "            [" . implode(", ", $values) . "],\n";
        }

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
