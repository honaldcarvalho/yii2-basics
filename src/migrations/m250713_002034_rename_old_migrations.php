<?php

use yii\db\Migration;

/**
 * Corrige nomes de migrations antigas na tabela de controle do Yii.
 */
class m250713_002034_rename_old_migrations extends Migration
{
    public function safeUp()
    {
        $renames = [
            'm270921_260307_add_column_status_to_files'         => 'm250621_260307_add_column_status_to_files',
            'm270921_260308_insert_default_meta_tags'           => 'm250621_260308_insert_default_meta_tags',
            'm270921_260309_add_parent_id_to_groups'            => 'm250621_260309_add_parent_id_to_groups',
            'm270921_260309_create_report_templates_table'      => 'm250621_260309_create_report_templates_table',
            'm270921_260309_fix_menu_ids_sequentially'          => 'm250622_260310_fix_menu_ids_sequentially',
            'm350807_161427_add_foreing_keys'                   => 'm250622_260312_add_foreign_keys',
        ];

        foreach ($renames as $old => $new) {
            $this->update('migration', ['version' => $new], ['version' => $old]);
        }
    }

    public function safeDown()
    {
        $reverts = [
            'm250621_260307_add_column_status_to_files'         => 'm270921_260307_add_column_status_to_files',
            'm250621_260308_insert_default_meta_tags'           => 'm270921_260308_insert_default_meta_tags',
            'm250621_260309_add_parent_id_to_groups'            => 'm270921_260309_add_parent_id_to_groups',
            'm250621_260309_create_report_templates_table'      => 'm270921_260309_create_report_templates_table',
            'm250622_260310_fix_menu_ids_sequentially'          => 'm270921_260309_fix_menu_ids_sequentially',
            'm250622_260312_add_foreign_keys'                   => 'm350807_161427_add_foreing_keys',
        ];

        foreach ($reverts as $new => $old) {
            $this->update('migration', ['version' => $old], ['version' => $new]);
        }
    }
}
