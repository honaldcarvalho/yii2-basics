<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\controllers\AuthorizationController;
use Yii;
use yii\helpers\Url;

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
 * @property string|null $path
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
            [['menu_id', 'order', 'only_admin', 'status'], 'integer'],
            [['label'], 'required'],
            [['label', 'icon', 'visible', 'icon_style'], 'string', 'max' => 60],
            [['url', 'path'], 'string', 'max' => 255],
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

    public static function getSidebarMenu($userIsAdmin = false)
    {
        $query = self::find()->where(['status' => 1]);
        if (!$userIsAdmin) {
            $query->andWhere(['only_admin' => 0]);
        }

        $menus = $query->orderBy(['order' => SORT_ASC])->asArray()->all();

        $items = [];
        $byId = [];

        // Controller atual (ex: "client")
        $currentController = strtolower((new \ReflectionClass(Yii::$app->controller))->getShortName());
        $currentController = preg_replace('/Controller$/', '', $currentController);
        $currentAction = Yii::$app->controller->action->id;

        foreach ($menus as $menu) {

            if ($menu['controller'] && $menu['action']) {
                if (!AuthorizationController::verAuthorization($menu['controller'], $menu['action'])) {
                    continue;
                } else {
                }
            }
            // Extrai o ID do controller do menu (ex: "ClientController" → "client")
            $menuControllerId = null;
            if (!empty($menu['controller'])) {
                try {
                    $menuControllerId = strtolower((new \ReflectionClass($menu['controller']))->getShortName());
                    $menuControllerId = preg_replace('/Controller$/', '', $menuControllerId);
                } catch (\ReflectionException $e) {
                    $menuControllerId = null;
                }
            }

            // Define se está ativo
            $menu['active'] = (
                $menuControllerId === $currentController &&
                ($menu['action'] === '*' || $menu['action'] === $currentAction)
            );

            $menu['children'] = [];
            // Verifica permissão com base no controller e action

            $byId[$menu['id']] = $menu;
        }

        // Monta hierarquia de menus
        foreach ($byId as $id => &$menu) {
            if ($menu['menu_id']) {
                if (isset($byId[$menu['menu_id']])) {
                    $byId[$menu['menu_id']]['children'][] = &$menu;
                }
            } else {
                $items[] = &$menu;
            }
        }

        // Propaga active do filho para o pai
        foreach ($byId as &$menu) {
            foreach ($menu['children'] as $child) {
                if ($child['active']) {
                    $menu['active'] = true;
                    break;
                }
            }
        }
        return $items;
    }

    public static function renderMenuItem($menu)
    {
        $hasChildren = !empty($menu['children']);
        $url = $menu['url'] ?? Url::to([$menu['controller'] . '/' . $menu['action']]);

        $html = '';
        if ($hasChildren) {
            $html .= '<li class="nav-item has-treeview ' . ($menu['active'] ? 'menu-open' : '') . '">';
            $html .= '<a href="#" class="nav-link ' . ($menu['active'] ? 'active' : '') . '">';
            $html .= '<i class="nav-icon ' . ($menu['icon'] ?? 'fas fa-circle') . '"></i>';
            $html .= '<p>' . $menu['label'] . ' <i class="right fas fa-angle-left"></i></p>';
            $html .= '</a>';
            $html .= '<ul class="nav nav-treeview">';
            foreach ($menu['children'] as $child) {
                $html .= self::renderMenuItem($child);
            }
            $html .= '</ul>';
            $html .= '</li>';
        } else {
            $html .= '<li class="nav-item">';
            $html .= '<a href="' . $url . '" class="nav-link ' . ($menu['active'] ? 'active' : '') . '">';
            $html .= '<i class="nav-icon ' . ($menu['icon'] ?? 'fas fa-circle') . '"></i>';
            $html .= '<p>' . $menu['label'] . '</p>';
            $html .= '</a>';
            $html .= '</li>';
        }

        return $html;
    }
}
