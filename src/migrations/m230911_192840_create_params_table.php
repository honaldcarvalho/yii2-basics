<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%params}}`.
 */
class m230911_192840_create_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%params}}', [
            'id' => $this->primaryKey(),
            'description' => $this->string()->notNull(),
            'language_id' => $this->integer()->notNull()->defaultValue(1),//'en-US'
            'group_id' => $this->integer()->notNull()->defaultValue(3),//clients
            'file_id' => $this->integer(),
            'email_service_id' => $this->integer(),
            'host' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'slogan' => $this->string()->notNull(),
            'bussiness_name' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'fone' => $this->string(),
            'cnpj' => $this->string(),
            'address' => $this->string(),
            'postal_code' => $this->string(),
            'ldap_login' => $this->integer()->defaultValue(0),
            'recaptcha_login' => $this->integer()->defaultValue(0),
            'recaptcha_secret_key' => $this->string(),
            'recaptcha_secret_site' => $this->string(),
            'meta_viewport' => $this->string()->notNull(),
            'meta_author' => $this->string()->notNull(),
            'meta_robots' => $this->string()->notNull(),
            'meta_googlebot' => $this->string()->notNull(),
            'meta_keywords' => $this->string(),
            'meta_description' => $this->string(),
            'canonical' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),
            'status'=> $this->integer()->defaultValue(1),
            'logging'=> $this->integer()->defaultValue(1)
        ]);

        $this->addForeignKey(
            'fk-params-language_id',
            'params',
            'language_id',
            'languages',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-params-file_id',
            'params',
            'file_id',
            'files',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-params-email_service_id',
            'params',
            'email_service_id',
            'email_services',
            'id',
            'RESTRICT'
        );
        
        $this->insert('params', [
            'meta_viewport' => 'width=device-width, initial-scale=1, shrink-to-fit=no',
            'meta_author' => 'Weebz',
            'meta_robots' => 'noindex,nofollow',
            'meta_googlebot' => 'noindex,nofollow',
            'canonical' => 'weebz.com.br',
            'slogan' => 'Yii2 System Basic',
            'title' => 'System Basic',
            'description' => 'system params',
            'host' => 'localhost',
            'title' => 'System Basic',
            'bussiness_name' => 'Weebz',
            'email' => 'honaldcarvalho@weebz.com.br',
            'email_service_id'=>1
        ]);

        $this->insert('menus', [
            'id'=> 14,
            'menu_id' => 1,
            'label'   => 'Params',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-cogs',
            'visible' => 'params;index',
            'url'     => '/params/index',
            'path'  => 'app',
            'active'  => 'params',
            'order'   => 2,
            'status'  => true
        ]);

    }

    /**

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%params}}');
    }
}
