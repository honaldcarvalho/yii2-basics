<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\models\ModelCommon;
use Yii;

/**
 * This is the model class for table "site_sections".
 *
 * @property int $id
 * @property string $name
 * @property int $order
 * @property int $status
 */
class SiteSection extends ModelCommon
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_sections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required','on'=>self::SCENARIO_DEFAULT],
            [['order', 'status'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'order' => Yii::t('app', 'Order'),
            'status' => Yii::t('app', 'Active'),
        ];
    }
}
