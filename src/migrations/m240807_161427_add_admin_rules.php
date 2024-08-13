<?php

use yii\db\Migration;

/**
 * Class m240807_161427_add_admin_rules
 */
class m240807_161427_add_admin_rules extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'site',
            'path' => 'app',
            'actions' => 'index;dashboard;dashboard-captive',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'menu',
            'path' => 'app',
            'actions' => 'create;delete;index;order-menu;update;view',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'folder',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete;edit;add',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'file',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete;list;upload;move;remove-file;delete-files;form;send;edit;add',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'group',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'language',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'message',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'source-message',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete;add-translation',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'user',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete;add-group;remove-group;profile;edit',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'rule',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'log',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'email-service',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete;test',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'params',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'license-type',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'license',
            'path' => 'app',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240807_161427_add_admin_rules cannot be reverted.\n";

        return false;
    }
}
