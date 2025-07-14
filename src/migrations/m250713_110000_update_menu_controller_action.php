<?php

use yii\db\Migration;

/**
 * Handles conversion of 'visible' to 'controller' and 'action', and removes 'visible' and 'path'.
 */
class m250713_110000_update_menu_controller_action extends Migration
{
    public function safeUp()
    {
        $this->addColumn('menus', 'controller', $this->string(255)->after('label'));
        $this->addColumn('menus', 'action', $this->string(60)->after('controller'));

        // Migração dos dados existentes
        $menus = (new \yii\db\Query())->from('menus')->all();

        foreach ($menus as $menu) {
            $visible = $menu['visible'] ?? null;
            $path = $menu['path'] ?? 'app';

            if ($visible && strpos($visible, ';') !== false) {
                [$controllerId, $action] = explode(';', $visible);
                $controllerBase = \yii\helpers\Inflector::id2camel($controllerId, '-') . 'Controller';

                switch ($path) {
                    case 'app':
                        $fqcn = "app\\controllers\\$controllerBase";
                        break;
                    case 'app/custom':
                        $fqcn = "app\\controllers\\custom\\$controllerBase";
                        break;
                    case 'weebz/controllers':
                        $fqcn = "weebz\\yii2basics\\controllers\\$controllerBase";
                        break;
                    default:
                        $fqcn = str_replace('/', '\\', $path) . "\\$controllerBase";
                        break;
                }

                Yii::$app->db->createCommand()->update('menus', [
                    'controller' => $fqcn,
                    'action' => $action,
                ], ['id' => $menu['id']])->execute();
            }
        }

        // Remover os campos antigos
        //$this->dropColumn('menus', 'visible');
        //$this->dropColumn('menus', 'path');
    }

    public function safeDown()
    {
        $this->addColumn('menus', 'visible', $this->string(60)->after('icon_style'));
        $this->addColumn('menus', 'path', $this->string(255)->after('url'));

        $menus = (new \yii\db\Query())->from('menus')->all();

        foreach ($menus as $menu) {
            $fqcn = $menu['controller'] ?? '';
            $action = $menu['action'] ?? '';
            $controllerId = '';

            if (preg_match('/\\\\(\w+)Controller$/', $fqcn, $matches)) {
                $controllerId = strtolower($matches[1]);
            }

            $path = 'app'; // valor padrão
            if (str_starts_with($fqcn, 'app\\controllers\\custom\\')) {
                $path = 'app/custom';
            } elseif (str_starts_with($fqcn, 'weebz\\yii2basics\\controllers\\')) {
                $path = 'weebz/controllers';
            }

            $visible = $controllerId && $action ? "$controllerId;$action" : null;

            Yii::$app->db->createCommand()->update('menus', [
                'visible' => $visible,
                'path' => $path,
            ], ['id' => $menu['id']])->execute();
        }

        $this->dropColumn('menus', 'controller');
        $this->dropColumn('menus', 'action');
    }
}
