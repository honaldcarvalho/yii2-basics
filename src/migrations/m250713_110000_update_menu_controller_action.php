<?php

use yii\db\Migration;
use yii\helpers\Inflector;

/**
 * Converts 'visible' to 'controller' and 'action'. Replaces 'visible' and 'path' columns.
 */
class m250713_110000_update_menu_controller_action extends Migration
{
    public function safeUp()
    {
        $this->addColumn('menus', 'controller', $this->string(255)->after('label'));
        $this->addColumn('menus', 'action', $this->string(60)->after('controller'));

        $menus = (new \yii\db\Query())->from('menus')->all();

        foreach ($menus as $menu) {
            $visible = $menu['visible'] ?? null;
            $path = $menu['path'] ?? 'app';

            if ($visible) {
                if (strpos($visible, ';') !== false) {
                    [$controllerId, $action] = explode(';', $visible);
                } else {
                    $controllerId = $visible;
                    $action = '*'; // quando não há action definida
                }

                $controllerBase = Inflector::id2camel($controllerId, '-') . 'Controller';

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

        // $this->dropColumn('menus', 'visible');
        // $this->dropColumn('menus', 'path');
    }

    public function safeDown()
    {
        $menus = (new \yii\db\Query())->from('menus')->all();

        foreach ($menus as $menu) {
            $fqcn = $menu['controller'] ?? '';
            $action = $menu['action'] ?? '*';
            $controllerId = '';

            if (preg_match('/\\\\(\w+)Controller$/', $fqcn, $matches)) {
                $controllerId = strtolower($matches[1]);
            }

            $path = 'app';
            if (str_starts_with($fqcn, 'app\\controllers\\custom\\')) {
                $path = 'app/custom';
            } elseif (str_starts_with($fqcn, 'weebz\\yii2basics\\controllers\\')) {
                $path = 'weebz/controllers';
            }

            $visible = $controllerId . ($action !== '*' ? ";$action" : '');

            Yii::$app->db->createCommand()->update('menus', [
                'visible' => $visible,
                'path' => $path,
            ], ['id' => $menu['id']])->execute();
        }

        $this->dropColumn('menus', 'controller');
        $this->dropColumn('menus', 'action');
    }
}
