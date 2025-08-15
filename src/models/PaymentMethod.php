<?php

namespace weebz\yii2basics\models;

use Yii;

/**
 * This is the model class for table "payment_methods".
 *
 * @property int $id
 * @property string $description
 * @property string $icon
 * @property string $tag
 * @property string|null $status
 *
 */
class PaymentMethod extends ModelCommon
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_methods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'icon', 'tag'], 'required','on'=>$this::SCENARIO_DEFAULT],
            [['status'], 'integer'],
            [['description', 'icon', 'tag', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'description' => Yii::t('app', 'Description'),
            'icon' => Yii::t('app', 'Icon'),
            'tag' => Yii::t('app', 'Tag'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

}
