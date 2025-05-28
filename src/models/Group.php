<?php

namespace weebz\yii2basics\models;

use Yii;

/**
 * This is the model class for table "groups".
 *
 * @property int $id
 * @property int $parent_id
 * @property string|null $name
 * @property int|null $status
 *
 * @property Project $project
 * @property Rules[] $rules
 * @property UserGroup[] $userGroups
 */
class Group extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['name', 'unique', 'targetClass' => 'weebz\yii2basics\models\Group'],
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
            'status' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Gets query for [[Rules]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRules()
    {
        return $this->hasMany(Rule::class, ['group_id' => 'id']);
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroup::class, ['group_id' => 'id']);
    }

    public function getParent()
    {
        return $this->hasOne(Group::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(Group::class, ['parent_id' => 'id']);
    }

    public static function getAllDescendantIds($groupIds)
    {
        $all = [];
        $queue = (array) $groupIds;

        while (!empty($queue)) {
            $current = array_shift($queue);
            if (!in_array($current, $all)) {
                $all[] = $current;
                $children = static::find()
                    ->select('id')
                    ->where(['parent_id' => $current])
                    ->column();
                $queue = array_merge($queue, $children);
            }
        }

        return $all;
    }
}
