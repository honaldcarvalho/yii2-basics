<?php

use weebz\yii2basics\models\Language;
use yii\helpers\Html;
$action = Yii::$app->controller->action->id;
$languages = Language::find()->where(['status'=>true])->all();
$script = <<< JS
  var toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
  var currentTheme = localStorage.getItem('theme');
  var mainHeader = document.querySelector('.main-header');

  if (currentTheme) {
    if (currentTheme === 'dark') {
      if (!document.body.classList.contains('dark-mode')) {
        document.body.classList.add("dark-mode");
      }
      if (mainHeader.classList.contains('navbar-light')) {
        mainHeader.classList.add('navbar-dark');
        mainHeader.classList.remove('navbar-light');
      }
      toggleSwitch.checked = true;
    }
  }

    function changeTheme(theme){
        $.ajax({
            type: "POST",
            url: "/user/change-theme",
            data:{theme:theme}
        });
    }

  function switchTheme(e) {
    if (e.target.checked) {
      if (!document.body.classList.contains('dark-mode')) {
        document.body.classList.add("dark-mode");
      }
      if (mainHeader.classList.contains('navbar-light')) {
        mainHeader.classList.add('navbar-dark');
        mainHeader.classList.remove('navbar-light');
      }
      localStorage.setItem('theme', 'dark');
    } else {
      if (document.body.classList.contains('dark-mode')) {
        document.body.classList.remove("dark-mode");
      }
      if (mainHeader.classList.contains('navbar-dark')) {
        mainHeader.classList.add('navbar-light');
        mainHeader.classList.remove('navbar-dark');
      }
      localStorage.setItem('theme', 'light');
    }
    if("$action" == 'dashboard-captive'){
        generateChartDay(localStorage.getItem('theme'));
        generateChartOs(localStorage.getItem('theme'));
        generateChartDevice(localStorage.getItem('theme'));
    }
    changeTheme(localStorage.getItem('theme'));
  }
  
  toggleSwitch.addEventListener('change', switchTheme, false);

JS;
$this->registerJs($script);

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-<?=$theme?>">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown" data-bs-theme="light">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="theme-menu" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Toggle theme">
                <i class="bi bi-translate"></i>
                <span class="ms-2" id="lang-selected">(<?= strtoupper(\Yii::$app->language); ?>)</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end lang-menu">
                <?php foreach($languages as $language): ?>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center <?= \Yii::$app->language == $language->code ? 'active' : ''; ?>" data-lang="<?= $language->code;?>" aria-pressed="false">
                      <i class="fas fa-caret-right"></i></i><span class="ms-2"><?= strtoupper($language->code);?></span>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <li class="nav-item">
            <div class="theme-switch-wrapper nav-link">
                <label class="theme-switch" for="checkbox">
                    <input type="checkbox" id="checkbox" wfd-invisible="true">
                    <span class="slider round"></span>
                </label>
            </div>
        </li>
        <li class="nav-item">
            <?= Html::a('<i class="fas fa-sign-out-alt"></i>', ['/site/logout'], ['data-method' => 'post', 'class' => 'nav-link']) ?>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

    </ul>
</nav>
<!-- /.navbar -->