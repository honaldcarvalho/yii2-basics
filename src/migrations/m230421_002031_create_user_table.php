<?php

use yii\db\Migration;

class m230421_002031_create_user_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer(),
            'file_id' => $this->integer(),
            'fullname' => $this->string(),
            'cpf_cnpj' => $this->string(18),
            'language_id' => $this->integer()->defaultValue(1),//en-US
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'verification_token' => $this->string()->defaultValue(null),
            'email' => $this->string()->notNull()->unique(),
            'phone' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull()->defaultValue(strtotime("now")),
            'updated_at' => $this->integer()->notNull()->defaultValue(strtotime("now")),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-user-group_id',
            'user',
            'group_id',
            'groups',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-user-file_id',
            'user',
            'file_id',
            'files',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-user-language_id',
            'user',
            'language_id',
            'languages',
            'id',
            'RESTRICT'
        );
        
        $this->insert('user', [
            'fullname' => 'Administrator',
            'username' => 'admin',
            'language_id' => 1,
            'email' => 'admin@email.com',
            'phone' => '55869999007567',
            'password_hash' => Yii::$app->security->generatePasswordHash('cpmAdmin@2023!'),
            'auth_key' => Yii::$app->security->generateRandomString(),
        ]);

        $this->insert('menus', [
            'id'=> 10,
            'menu_id' => 2,
            'label'   => 'Users',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-user',
            'visible' => 'user;index',
            'url'     => '/user/index',
            'path'  => 'app',
            'active'  => 'user',
            'order'   => 1,
            'status'  => true
        ]);


    }
    

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
