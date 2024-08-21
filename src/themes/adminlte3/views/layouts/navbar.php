<?php

use yii\helpers\Html;
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