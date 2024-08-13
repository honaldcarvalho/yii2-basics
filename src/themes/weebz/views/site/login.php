<?php
/* @var $Yii Yii */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model weebz\yii2basics\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => "<div class=\"col-lg-offset-1 col-lg-12\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
        ]) ?>
    
        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-12">
                <?= Html::submitButton('Login', ['class' => 'btn btn-weebz', 'name' => 'login-button']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>

        <p class="mb-1">
            <a href="forgot-password.html">I forgot my password</a>
        </p>
        <p class="mb-0">
            <a href="/signup" class="text-center">Register a new membership</a>
        </p>

</div>
