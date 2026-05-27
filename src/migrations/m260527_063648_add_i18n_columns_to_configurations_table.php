<?php

use yii\db\Migration;

/**
 * Class m260527_063648_add_i18n_columns_to_configurations_table
 */
class m260527_063648_add_i18n_columns_to_configurations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%configurations}}', 'i18n_api_url', $this->string(255)->null());
        $this->addColumn('{{%configurations}}', 'i18n_api_token', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%configurations}}', 'i18n_api_url');
        $this->dropColumn('{{%configurations}}', 'i18n_api_token');
    }
}
