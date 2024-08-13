<?php

namespace weebz\yii2basics\modules\common\models;

use Yii;

/**
 * This is the model class for table "menus".
 *
 * @property int $id
 * @property int|null $menu_id
 * @property string $label
 * @property string|null $icon
 * @property string|null $icon_style
 * @property string|null $visible
 * @property string|null $url
 * @property string|null $active
 * @property int $order
 * @property int|null $onlyAdmin
 * @property int|null $status
 *
 * @property Menu $menu
 * @property Menu[] $menus
 */
class Menu extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['menu_id', 'order','only_admin', 'status'], 'integer'],
            [['label'], 'required'],
            [['label', 'icon', 'visible','icon_style'], 'string', 'max' => 60],
            [['url'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 60],
            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::class, 'targetAttribute' => ['menu_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'menu_id' => Yii::t('app', 'Menu ID'),
            'label' => Yii::t('app', 'Label'),
            'icon' => Yii::t('app', 'Icon'),
            'icon_style' => Yii::t('app', 'Icon Style'),
            'visible' => Yii::t('app', 'Visible'),
            'url' => Yii::t('app', 'Url'),
            'active' => Yii::t('app', 'Active'),
            'order' => Yii::t('app', 'Order'),
            'only_admin' => Yii::t('app', 'Only Administrators'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Gets query for [[Menu]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::class, ['id' => 'menu_id']);
    }

    /**
     * Gets query for [[Menus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::class, ['menu_id' => 'id']);
    }
}
