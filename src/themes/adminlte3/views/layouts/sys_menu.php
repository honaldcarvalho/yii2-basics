<?php
use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\controllers\AuthorizationController as Authz;
use app\models\SysMenu;
use weebz\yii2basics\models\Configuration;
use weebz\yii2basics\widgets\Menu as WidgetsMenu;

if (Yii::$app->user->isGuest) return false;

$params = Configuration::get();
$this->registerJS(<<<JS
JS);

$name_split = explode(' ', Yii::$app->user->identity->fullname);
$name_user  = $name_split[0] . (isset($name_split[1]) ? ' ' . end($name_split) : '');

$assetsDir = ControllerCommon::getAssetsDir();
if (!empty($params->file_id) && $params->file !== null) {
    $url = Yii::getAlias('@web') . $params->file->urlThumb;
    $login_image = "<img alt='{$params->title}' class='brand-image img-circle elevation-3' src='{$url}' style='opacity:.8' />";
} else {
    $login_image = "<img src='{$assetsDir}/img/croacworks-logo-hq.png' alt='{$params->title}' class='brand-image elevation-3' style='opacity:.8'>";
}

/** Verificação de permissão: controller FQCN + actions ("a;b;c" ou "*") */
function canSeeMenuItem(?string $controllerFQCN, ?string $actions): bool
{
    if (Authz::isGuest()) return false;
    if (Authz::isAdmin()) return true;

    $controllerFQCN = trim((string)$controllerFQCN);
    $actions        = trim((string)$actions);

    if ($controllerFQCN === '') return false;

    if ($actions === '' || $actions === '*') {
        // Qualquer permissão no controller já habilita a visualização
        $groups = Authz::getUserGroups() ?? [];
        return \weebz\yii2basics\models\Role::find()
            ->where(['controller' => $controllerFQCN, 'status' => 1])
            ->andWhere(['in', 'group_id', $groups])
            ->exists();
    }

    foreach (explode(';', $actions) as $act) {
        $act = trim($act);
        if ($act === '') continue;
        if (Authz::verAuthorization($controllerFQCN, $act)) return true;
    }
    return false;
}

/** Monta nós recursivamente a partir de sys_menus */
function getNodes($parentId = null): array
{
    $items = SysMenu::find()
        ->where(['parent_id' => $parentId, 'status' => true])
        ->orderBy(['order' => SORT_ASC])
        ->all();

    $nodes = [];
    $currentFQCN   = get_class(Yii::$app->controller);
    $currentAction = Yii::$app->controller->action->id;

    foreach ($items as $item) {
        $children = getNodes($item->id);
        $isGroup  = ($item->url === '#');

        // Somente admin?
        if ($item->only_admin && !Authz::isAdmin()) {
            continue;
        }

        // Visibilidade
        if ($isGroup) {
            $isVisible = false;
            foreach ($children as $c) {
                if (!empty($c['visible'])) { $isVisible = true; break; }
            }
        } else {
            $isVisible = canSeeMenuItem($item->controller, $item->action);
        }

        // Active
        $active = false;
        if (!$isGroup && $item->controller) {
            $actions = trim((string)$item->action);
            if ($item->controller === $currentFQCN) {
                $active = ($actions === '' || $actions === '*')
                    ? true
                    : in_array($currentAction, array_map('trim', explode(';', $actions)), true);
            }
        }

        $node = [
            'label'     => Yii::t('app', $item->label),
            'icon'      => (string)$item->icon,
            'iconStyle' => (string)$item->icon_style,
            'url'       => [$item->url ?: '#'],
            'visible'   => $isVisible,
        ];

        if ($isGroup) {
            $node['items'] = $children;
        } else {
            $node['active'] = $active;
        }

        if ($isVisible || ($isGroup && !empty($children))) {
            $nodes[] = $node;
        }
    }

    return $nodes;
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= Yii::getAlias('/'); ?>" class="brand-link">
        <?= $login_image ?>
        <span class="brand-text font-weight-light"><?= $params->title ?></span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image user-image">
                <?php if (Yii::$app->user->identity->file): ?>
                    <img class="brand-image img-circle elevation-2" src="<?= Yii::$app->user->identity->file->url; ?>" style="width:32px;opacity:.8" />
                <?php else: ?>
                    <i class="fas fa-user-circle img-circle elevation-2" alt="User Image"></i>
                <?php endif; ?>
            </div>
            <div class="info">
                <?= yii\helpers\Html::a($name_user, ['/user/profile', 'id' => Yii::$app->user->identity->id], ["class" => "d-block"]) ?><br>
            </div>
        </div>

        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="<?= Yii::t('app', 'Search') ?>" aria-label="<?= Yii::t('app', 'Search') ?>">
                <div class="input-group-append">
                    <button class="btn btn-sidebar"><i class="fas fa-search fa-fw"></i></button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <?php
                $nodes = getNodes(null);
                echo WidgetsMenu::widget([
                    'options' => [
                        'class' => 'nav nav-pills nav-sidebar flex-column nav-child-indent',
                        'data-widget' => 'treeview',
                        'role' => 'menu',
                        'data-accordion' => 'false'
                    ],
                    'items' => array_merge($nodes, [
                        ['label' => 'Logout', 'icon' => 'fas fa-sign-out-alt', 'url' => ['/site/logout']]
                    ]),
                ]);
            ?>
        </nav>
    </div>
</aside>
