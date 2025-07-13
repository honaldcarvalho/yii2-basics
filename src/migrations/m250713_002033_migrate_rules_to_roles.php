<?php

use yii\db\Migration;
use yii\helpers\ArrayHelper;

/**
 * Migra dados da tabela `rules` para `roles` com namespace completo no controller.
 */
class m250713_002033_migrate_rules_to_roles extends Migration
{
    public function safeUp()
    {
        $rules = (new \yii\db\Query())->from('rules')->all();

        foreach ($rules as $rule) {
            // Converte controller + path para FQCN
            $fqcn = self::convertToFQCN($rule['path'], $rule['controller']);

            // Insere em roles
            $this->insert('roles', [
                'user_id'     => $rule['user_id'],
                'group_id'    => $rule['group_id'],
                'controller'  => $fqcn,
                'actions'     => $rule['actions'],
                'origin'      => $rule['origin'] ?? '*',
                'created_at'  => $rule['created_at'],
                'updated_at'  => $rule['updated_at'],
                'status'      => $rule['status'],
            ]);
        }
    }

    public function safeDown()
    {
        // Apenas limpa os dados migrados
        $this->delete('roles');
    }

    /**
     * Converte o path + controller para FQCN (namespace\ClasseController).
     */
    private static function convertToFQCN($path, $controller)
    {
        $baseNamespace = match (trim($path)) {
            'app' => 'app\\controllers',
            'weebz/controllers' => 'weebz\\yii2basics\\controllers',
            default => trim(str_replace('/', '\\', $path))
        };

        // Trata casos onde já está no formato controller-name
        $camelized = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $controller)));

        return $baseNamespace . '\\' . $camelized . 'Controller';
    }
}
