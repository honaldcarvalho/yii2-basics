<?php

use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\controllers\AuthorizationController;
use weebz\yii2basics\models\Menu;
use weebz\yii2basics\models\Configuration;
use weebz\yii2basics\widgets\Menu as WidgetsMenu;
use yii\helpers\Url;
use yii\web\View;

if (Yii::$app->user->isGuest) {
    return false;
}

$userIsAdmin = Yii::$app->user->identity->isAdmin ?? false;
$menus = Menu::getSidebarMenu($userIsAdmin);

$params = Configuration::get();
$this->registerJS("");

$name_user = '';
$controller_id = Yii::$app->controller->id;
if (!Yii::$app->user->isGuest) {
    $name_split = explode(' ', Yii::$app->user->identity->fullname);
    $name_user = $name_split[0] . (isset($name_split[1]) ? ' ' . end($name_split) : '');
}

$assetsDir = ControllerCommon::getAssetsDir();
if (!empty($params->file_id) && $params->file != null) {
    $url = Yii::getAlias('@web') . $$menusparams->file->urlThumb;
    $login_image = "<img alt='{$params->title}' class='brand-image img-circle elevation-3' src='{$url}' style='opacity: .8' />";
} else {
    $login_image = "<img src='{$assetsDir}/img/croacworks-logo-hq.png' alt='{$params->title}' class='brand-image elevation-3' style='opacity: .8'>";
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
                    <img class='brand-image img-circle elevation-2' src="<?= Yii::$app->user->identity->file->url; ?>" style='width:32px; opacity: .8' />
                <?php else: ?>
                    <i class="fas fa-user-circle img-circle elevation-2" alt="User Image"></i>
                <?php endif; ?>
            </div>
            <div class="info">
                <?= yii\helpers\Html::a($name_user, ['/user/profile', 'id' => Yii::$app->user->identity->id], ["class" => "d-block"]) ?><br>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <!-- href be escaped -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="<?= Yii::t('app', 'Search') ?>" aria-label="<?= Yii::t('app', 'Search') ?>">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="false" role="menu">
                <?php
                foreach ($menus as $menu) {
                    echo Menu::renderMenuItem($menu);
                }
                ?>
            </ul>
        </nav>
    </div>
</aside>