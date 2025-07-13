<?php

use yii\db\Migration;

/**
 * Class m270921_260309_fix_menu_ids_sequentially
 */
class m250622_260310_fix_menu_ids_sequentially extends Migration
{
    public function safeUp()
    {
        $this->execute('SET foreign_key_checks = 0;');

        $menus = (new \yii\db\Query())
            ->from('menus')
            ->orderBy(['menu_id' => SORT_ASC, 'order' => SORT_ASC])
            ->all();

        // Mapeamento antigo → novo
        $oldToNewId = [];
        $idCounter = 1;

        foreach ($menus as $menu) {
            $oldId = $menu['id'];
            $newId = $idCounter++;
            $oldToNewId[$oldId] = $newId;
        }

        // Apaga tudo e reinserir com IDs corrigidos
        $this->delete('menus');

        foreach ($menus as $menu) {
            $newId = $oldToNewId[$menu['id']];
            $newParentId = $menu['menu_id'] !== null ? ($oldToNewId[$menu['menu_id']] ?? null) : null;

            Yii::$app->db->createCommand()->insert('menus', [
                'id'         => $newId,
                'menu_id'    => $newParentId,
                'label'      => $menu['label'],
                'icon_style' => $menu['icon_style'],
                'icon'       => $menu['icon'],
                'visible'    => $menu['visible'],
                'url'        => $menu['url'],
                'active'     => $menu['active'],
                'path'       => $menu['path'],
                'order'      => $menu['order'],
                'only_admin' => $menu['only_admin'],
                'status'     => $menu['status'],
            ])->execute();
        }
        $this->execute('SET foreign_key_checks = 1;');
    }

    public function safeDown()
    {
        echo "Esta migration não pode ser revertida automaticamente. Faça backup antes de aplicá-la.\n";
        return false;
    }
}
