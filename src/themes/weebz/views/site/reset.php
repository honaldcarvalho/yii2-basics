<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\modules\common\models\Usuario */

$this->title = 'Alteração de Senha';
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_password', [
        'model' => $model,
    ]) ?>

</div>
