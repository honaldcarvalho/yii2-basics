<?php

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\models\Section;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Section */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="section-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'section_id')->dropDownList(
        yii\helpers\ArrayHelper::map(Section::find()->where(['in','group_id',AuthController::userGroups()])->all(), 'id', 'name'), 
        ['prompt' => '-- selecione uma secção --']) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uri')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
