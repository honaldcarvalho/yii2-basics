<?php

namespace weebz\yii2basics\models;

use Yii;

/**
 * This is the model class for table "configuration_parameters".
 *
 * @property int $id
 * @property int $configuration_id
 * @property int $parameter_id
 *
 * @property Configuration $configuration
 * @property Parameter $parameter
 */
class ConfigurationParameter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'configuration_parameters';
    }
}