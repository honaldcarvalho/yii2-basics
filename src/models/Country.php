<?php

namespace weebz\yii2basics\models;

use Yii;

/**
 * This is the model class for table "countries".
 *
 * @property int $id
 * @property string $name
 * @property string $name_pt
 * @property string|null $abbreviation
 * @property int|null $bacen
 * @property int|null $status
 *
 * @property States[] $states
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'countries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'name_pt'], 'required'],
            [['bacen', 'status'], 'integer'],
            [['name', 'name_pt'], 'string', 'max' => 60],
            [['abbreviation'], 'string', 'max' => 2],
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
            'name_pt' => Yii::t('app', 'Name Pt'),
            'abbreviation' => Yii::t('app', 'Abbreviation'),
            'bacen' => Yii::t('app', 'Bacen'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Gets query for [[States]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStates()
    {
        return $this->hasMany(State::class, ['country_id' => 'id']);
    }
}
