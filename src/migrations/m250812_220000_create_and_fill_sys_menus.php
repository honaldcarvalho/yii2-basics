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
        // 1) Cria nova tabela
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
            'controller'  => $this->string(255)->null(),   // FQCN
            'action'      => $this->string(255)->null(),   // "index" | "index;view" | "*"
        ]);

        // Indexes úteis
        $this->createIndex('idx-sys_menus-parent_id', $this->new, 'parent_id');
        $this->createIndex('idx-sys_menus-status',    $this->new, 'status');
        $this->createIndex('idx-sys_menus-controller',$this->new, 'controller');

        // 2) Copia e adapta
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
            $visible    = trim((string)($r['visible'] ?? ''));
            $path       = trim((string)($r['path'] ?? ''));

            // Grupos/headers
            if ($url === '#') {
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
                    'controller' => null,
                    'action'     => null,
                ]);
                continue;
            }

            // Caso já exista controller na tabela antiga, preserva (e garante action padrão)
            if ($controller !== '') {
                if ($action === '') {
                    $action = '*';
                }
            } else {
                // Decide base namespace a partir do 'path'
                $nsBase = (str_starts_with($path, 'app'))
                    ? 'app\\controllers\\'
                    : 'weebz\\yii2basics\\controllers\\';

                $ok = false;

                // 1) Tenta extrair do visible: "fqcn;actions" ou "controller-id;actions"
                if ($visible !== '') {
                    [$ctrlToken, $actToken] = array_pad(explode(';', $visible, 2), 2, '');
                    $ctrlToken = trim($ctrlToken);
                    $actToken  = trim($actToken);

                    if ($ctrlToken !== '') {
                        if (strpos($ctrlToken, '\\') !== false) {
                            // Já é FQCN (mantém como veio)
                            $controller = $ctrlToken;
                        } else {
                            // controller-id → Classe + nsBase
                            $class = Inflector::id2camel($ctrlToken) . 'Controller';
                            $controller = $nsBase . $class;
                        }
                        $action = $actToken !== '' ? $actToken : ($action !== '' ? $action : '*');
                        $ok = true;
                    }
                }

                // 2) Senão, tenta inferir pela URL (/foo/bar → FooController + action "bar")
                if (!$ok && $url && $url !== '#') {
                    $seg = explode('/', ltrim($url, '/'));
                    $first = $seg[0] ?? '';
                    if ($first !== '') {
                        $class = Inflector::id2camel($first) . 'Controller';
                        $controller = $nsBase . $class;
                        // se existir segunda parte na URL, usa como action; senão *
                        $action = $action !== '' ? $action : (($seg[1] ?? '') !== '' ? $seg[1] : '*');
                    }
                }

                // Fallback final
                if ($controller === '') {
                    $controller = null;
                }
                if ($action === '') {
                    $action = null;
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
                'controller' => $controller ?: null,
                'action'     => $action ?: null,
            ]);
        }

        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function safeDown()
    {
        // Remove a tabela nova
        $this->dropTable($this->new);
    }
}
