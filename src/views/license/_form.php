<?php

use weebz\yii2basics\models\Group;;
use weebz\yii2basics\models\LicenseType;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\License */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="license-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'license_type_id')->dropDownList(yii\helpers\ArrayHelper::map(LicenseType::find()
            ->select('id,name')->asArray()->all(), 
            'id', 'name'),['prompt'=>' -- License Type --']) ?>

    <?= $form->field($model, 'group_id')->dropDownList(yii\helpers\ArrayHelper::map(Group::find()
            ->select('id,name')->asArray()->all(), 
            'id', 'name'),['prompt'=>' -- Group --']) ?>

    <?= $form->field($model, 'validate')->input('date') ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
