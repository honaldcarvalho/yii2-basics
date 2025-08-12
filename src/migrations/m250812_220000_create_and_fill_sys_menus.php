<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Inflector;

class m250812_220000_create_and_fill_sys_menus extends Migration
{
    private string $old = '{{%menus}}';
    private string $new = '{{%sys_menus}}';

    public function safeUp()
    {
        // 1) Tabela nova
        $this->createTable($this->new, [
            'id'          => $this->primaryKey(),
            'parent_id'   => $this->integer()->null(),
            'label'       => $this->string(255)->notNull(),
            'icon'        => $this->string(128)->null(),
            'icon_style'  => $this->string(128)->null(),
            'url'         => $this->string(255)->notNull()->defaultValue('#'),
            'order'       => $this->integer()->notNull()->defaultValue(0),
            'only_admin'  => $this->boolean()->notNull()->defaultValue(false),
            'status'      => $this->boolean()->notNull()->defaultValue(true),
            'show'        => $this->boolean()->notNull()->defaultValue(true),  // hard toggle
            'controller'  => $this->string(255)->null(),   // FQCN
            'action'      => $this->string(255)->null(),   // usado p/ "active"
            'visible'     => $this->string(255)->null(),   // CSV de actions p/ exibição
        ]);

        $this->createIndex('idx-sys_menus-parent_id',  $this->new, 'parent_id');
        $this->createIndex('idx-sys_menus-status',     $this->new, 'status');
        $this->createIndex('idx-sys_menus-show',       $this->new, 'show');
        $this->createIndex('idx-sys_menus-controller', $this->new, 'controller');

        // 2) Copiar/adaptar
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $rows = (new Query())->from($this->old)->orderBy(['id' => SORT_ASC])->all();

        foreach ($rows as $r) {
            $id         = (int)$r['id'];
            $parentId   = $r['menu_id'] ?? null;
            $label      = (string)$r['label'];
            $icon       = (string)($r['icon'] ?? '');
            $iconStyle  = (string)($r['icon_style'] ?? '');
            $url        = (string)($r['url'] ?? '#');
            $order      = (int)($r['order'] ?? 0);
            $onlyAdmin  = (int)($r['only_admin'] ?? 0);
            $status     = (int)($r['status'] ?? 1);

            $controller = trim((string)($r['controller'] ?? ''));
            $action     = trim((string)($r['action'] ?? ''));
            $visibleRaw = trim((string)($r['visible'] ?? ''));
            $path       = trim((string)($r['path'] ?? ''));

            $isGroup = ($url === '#');

            if ($isGroup) {
                $this->insert($this->new, [
                    'id'         => $id,
                    'parent_id'  => $parentId ?: null,
                    'label'      => $label,
                    'icon'       => $icon ?: null,
                    'icon_style' => $iconStyle ?: null,
                    'url'        => '#',
                    'order'      => $order,
                    'only_admin' => (bool)$onlyAdmin,
                    'status'     => (bool)$status,
                    'show'       => true,
                    'controller' => null,
                    'action'     => null,
                    'visible'    => null,
                ]);
                continue;
            }

            // Deduz controller/action
            if ($controller === '') {
                $nsBase = (str_starts_with($path, 'app'))
                    ? 'app\\controllers\\'
                    : 'weebz\\yii2basics\\controllers\\';

                $filled = false;

                if ($visibleRaw !== '') {
                    $parts = array_values(array_filter(array_map('trim', explode(';', $visibleRaw)), 'strlen'));
                    if (!empty($parts)) {
                        // Se primeiro parecer um controller, usa-o
                        $first = $parts[0];
                        if (strpos($first, '\\') !== false || stripos($first, 'controller') !== false) {
                            $controller = $first;
                            $filled = true;
                        } elseif ($first !== '' && preg_match('/^[a-z0-9\-]+$/', $first)) {
                            // Pode ser id de controller
                            $class = Inflector::id2camel($first) . 'Controller';
                            $controller = $nsBase . $class;
                            $filled = true;
                        }
                    }
                }

                if (!$filled && $url && $url !== '#') {
                    $seg = explode('/', ltrim($url, '/'));
                    $first = $seg[0] ?? '';
                    if ($first !== '') {
                        $class = Inflector::id2camel($first) . 'Controller';
                        $controller = $nsBase . $class;
                        $action = $action !== '' ? $action : (($seg[1] ?? '') !== '' ? $seg[1] : '*');
                    }
                }
            } else {
                if ($action === '') $action = '*';
            }

            // Normaliza visible (apenas actions)
            $visible = null;
            if ($visibleRaw !== '') {
                $parts = array_values(array_filter(array_map('trim', explode(';', $visibleRaw)), 'strlen'));
                if (!empty($parts)) {
                    // Extrai id do controller deduzido
                    $ctrlId = null;
                    if ($controller && preg_match('#\\\\controllers\\\\([^\\\\]+)Controller$#', $controller, $m)) {
                        $ctrlId = Inflector::camel2id($m[1]);
                    }

                    // Se o primeiro token "bate" com controller (id, FQCN ou NomeController), descarta-o
                    $first = $parts[0];
                    $isCtrlToken =
                        ($ctrlId && $first === $ctrlId) ||
                        ($controller && $first === $controller) ||
                        (stripos($first, 'controller') !== false);

                    $actions = $isCtrlToken ? array_slice($parts, 1) : $parts;
                    $visible = !empty($actions) ? implode(';', $actions) : null;
                }
            }

            $this->insert($this->new, [
                'id'         => $id,
                'parent_id'  => $parentId ?: null,
                'label'      => $label,
                'icon'       => $icon ?: null,
                'icon_style' => $iconStyle ?: null,
                'url'        => $url ?: '#',
                'order'      => $order,
                'only_admin' => (bool)$onlyAdmin,
                'status'     => (bool)$status,
                'show'       => true,
                'controller' => $controller ?: null,
                'action'     => $action ?: null,
                'visible'    => $visible, // só actions
            ]);
        }

        $this->execute('SET FOREIGN_KEY_CHECKS=1');

        // 3) FK de parent_id
        $this->addForeignKey(
            'fk-sys_menus-parent',
            $this->new,
            'parent_id',
            $this->new,
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema($this->new, true) !== null) {
            $this->dropForeignKey('fk-sys_menus-parent', $this->new);
        }
        $this->dropTable($this->new);
    }
}
