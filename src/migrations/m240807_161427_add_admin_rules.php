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
            'actions' => 'index;dashboard',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'site',
            'path' => 'weebz/controllers',
            'actions' => 'index;dashboard',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'menu',
            'path' => 'weebz/controllers',
            'actions' => 'create;delete;index;order-menu;update;view',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'folder',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete;edit;add',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'file',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete;list;upload;move;remove-file;delete-files;form;send;edit;add',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'group',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'language',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'message',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'source-message',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete;add-translation',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'user',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete;add-group;remove-group;profile;edit',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'rule',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'log',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'email-service',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete;test',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'params',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'license-type',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'license',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'page',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'section',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'notification',
            'path' => 'weebz/controllers',
            'actions' => 'index;create;view;update;delete',
            'status'=>true
        ]);

        $this->insert('rules', [
            'group_id' => 2,
            'controller' => 'notification-message',
            'path' => 'weebz/controllers',
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
