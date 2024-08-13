<?php
/* @var $Yii Yii */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model weebz\yii2basics\models\SignupForm */
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'form-signup',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'email') ?>

            <?= $form->field($model, 'phone') ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

    
        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-12">
                <?= Html::submitButton('Signup', ['class' => 'btn btn-weebz', 'name' => 'signup-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
