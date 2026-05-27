<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Configuration $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'i18n Settings';
$this->params['breadcrumbs'][] = ['label' => 'Configurations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="configuration-i18n">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="configuration-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'i18n_api_url')->textInput(['maxlength' => true])->label('API URL') ?>

        <?= $form->field($model, 'i18n_api_token')->textarea(['rows' => 6])->label('API Token') ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
