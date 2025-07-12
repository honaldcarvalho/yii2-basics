<?php

namespace weebz\yii2basics\models;

use Yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "rules".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $group_id
 * @property string $path
 * @property string $controller
 * @property string $actions
 * @property string $origin
 * @property int|null $status
 *
 * @property Groups $group
 * @property Users $user
 */
class Rule extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id', 'status'], 'integer'],
            [['controller', 'actions'], 'required'],
            [['controller'], 'string', 'max' => 255],
            [['origin'], 'string'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'controller' => Yii::t('app', 'Controller'),
            'actions' => Yii::t('app', 'Actions'),
            'origin' => Yii::t('app', 'Origin'),
            'status' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    
    
}