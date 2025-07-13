<?php

namespace weebz\yii2basics\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "roles".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $group_id
 * @property string $controller
 * @property string|null $actions
 * @property string $origin
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $status
 *
 * @property User $user
 * @property Group $group
 */
class Role extends ModelCommon
{
    public static function tableName()
    {
        return 'roles';
    }

    public function rules()
    {
        return [
            [['controller'], 'required','on'=> 'CREATE'],
            [['controller', 'actions', 'origin'], 'string'],
            [['user_id', 'group_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'group_id' => Yii::t('app', 'Group'),
            'controller' => Yii::t('app', 'Controller (with namespace)'),
            'actions' => Yii::t('app', 'Actions'),
            'origin' => Yii::t('app', 'Origin (URLs)'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);

        if ($skipIfSet && $this->origin !== null) {
            return $this;
        }

        $this->origin = '*';
        return $this;
    }
}
