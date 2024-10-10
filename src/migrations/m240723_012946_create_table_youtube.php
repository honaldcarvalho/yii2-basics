<?php

use weebz\yii2basics\models\Menu;
use yii\db\Migration;

/**
 * Handles the creation for table `youtube`.
 */
class m240723_012946_create_table_youtube extends Migration
{

    /** @var string  */
    protected $tableName = 'youtube';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $collation = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $collation = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->string(50)->notNull(),
            'group_id' => $this->integer(),
            'title' => $this->string(255),
            'description' => $this->string(255),
            'thumbnail' => $this->string(255),
            'created_at' => $this->datetime(),
            'updated_at' => $this->datetime(),
            'status' => $this->smallInteger(6)->defaultValue(1),
        ], $collation);

        $maxId = Menu::find()->where('id < 99')->max('id');
        $id =  $maxId + 1;

        $this->insert('menus', [
            'id'=> $id,
            'menu_id' => null,
            'label'   => 'Videos',
            'icon_style'=> 'fas',
            'icon'    => 'fas fa-youtube',
            'visible' => 'videos;index',
            'url'     => '/video/index',
            'path'  => 'app',
            'active'  => 'video',
            'order'   => 0,
            'status'  => true
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
