<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%sections}}`.
 */
class m231221_234223_add_background_image_column_to_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%params}}', 'background_image', $this->integer());
        $this->addForeignKey(
            'fk-params-background_image',
            'params',
            'background_image',
            'files',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-params-background_image',
            'params',
        );
        $this->dropColumn('{{%params}}', 'background_image');
    }
}
