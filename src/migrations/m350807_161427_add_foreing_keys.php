<?php

use yii\db\Migration;

/**
 * Class m240807_161427_add_admin_rules
 */
class m350807_161427_add_foreing_keys extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        /**
         * configurations keys
         */
        $this->addForeignKey(
            'fk-configurations-language_id',
            'configurations',
            'language_id',
            'languages',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-configurations-file_id',
            'configurations',
            'file_id',
            'files',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-configurations-email_service_id',
            'configurations',
            'email_service_id',
            'email_services',
            'id',
            'RESTRICT'
        );

        /**
         * configurations params
         */

         $this->addForeignKey(
            'fk-params-configuration_id',
            'params',
            'configuration_id',
            'configurations',
            'id',
            'RESTRICT'
        );

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