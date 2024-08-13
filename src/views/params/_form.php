<?php

use weebz\yii2basics\models\EmailService;
use weebz\yii2basics\models\Group;;
use weebz\yii2basics\models\Language;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Param */
/* @var $form yii\bootstrap4\ActiveForm */
?>
<?php $form = ActiveForm::begin(['class' => 'row mb-5']); ?>
<div class="row">

    <div class="col-md-4">
        <?= $form->field($model, 'file_id')->hiddenInput() ?>  
        <?= weebz\yii2basics\widgets\SelectFile::widget(['field' => '#params-file_id', 'preview_image' => 1, 'extensions' => ['jpeg', 'jpg', 'png'], 'model' => $model]); ?>
    </div>
    
    <div class="col-md-8">
        <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'group_id')->dropdownList(yii\helpers\ArrayHelper::map(Group::find()->asArray()->all(), 'id', 'name')) ?>
        <?= $form->field($model, 'language_id')->dropdownList(yii\helpers\ArrayHelper::map(Language::find()->asArray()->all(), 'id', 'name')) ?>
        <?= $form->field($model, 'email_service_id')->dropdownList(yii\helpers\ArrayHelper::map(EmailService::find()->asArray()->all(), 'id', 'description'),['prompt'=>'-- NÃO SELECIONADO --']) ?>
        <?= $form->field($model, 'host')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'slogan')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'canonical')->textInput(['maxlength' => true]) ?>
    </div> 

    <div class="col-md-6">
        <?= $form->field($model, 'bussiness_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'fone')->textInput(['maxlength' => true]) ?>
    </div>
    
    <div class="col-md-6">
        <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'postal_code')->textInput(['maxlength' => true]) ?>
    </div>
    
    <div class="col-md-6">
        <?= $form->field($model, 'recaptcha_login')->checkbox() ?>
    </div>
    
    <div class="col-md-6">
        <?= $form->field($model, 'recaptcha_secret_site')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'recaptcha_secret_key')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'meta_viewport')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'meta_author')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'meta_robots')->textInput(['maxlength' => true]) ?>
    </div>
    
    <div class="col-md-6">
        <?= $form->field($model, 'meta_googlebot')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'meta_keywords')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'meta_description')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'ldap_login')->checkbox() ?>
        <?= $form->field($model, 'logging')->checkbox() ?>
        <?= $form->field($model, 'status')->checkbox() ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>