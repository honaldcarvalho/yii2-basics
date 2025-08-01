<?php

use weebz\yii2basics\models\EmailService;
use weebz\yii2basics\models\File;
use weebz\yii2basics\models\Group;;
use weebz\yii2basics\models\Language;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Configuration */
/* @var $form yii\bootstrap5\ActiveForm */
$image = '';
$display = 'display:none';
if(!empty($model->file_id && $model->file != null)){
    $image = File::findOne($model->file_id)->url;
}
?>
<?php $form = ActiveForm::begin(['class' => 'row mb-5']); ?>
<div class="row">

    <div class="col-sm-12">
        <?= $form->field($model, 'file_id')->fileInput() ?>
        <?= \weebz\yii2basics\widgets\UploadFoto::widget([
            'imagem'=> $image,
            'fileField'=>'configuration-file_id',
            'aspectRatio'=>'1'
        ])?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'group_id')->dropdownList(yii\helpers\ArrayHelper::map(Group::find()->asArray()->all(), 'id', 'name')) ?>
        <?= $form->field($model, 'language_id')->dropdownList(yii\helpers\ArrayHelper::map(Language::find()->asArray()->all(), 'id', 'name')) ?>
        <?= $form->field($model, 'email_service_id')->dropdownList(yii\helpers\ArrayHelper::map(EmailService::find()->asArray()->all(), 'id', 'description'),['prompt'=>'-- NÃO SELECIONADO --']) ?>
        <?= $form->field($model, 'host')->textInput(['maxlength' => true]) ?>
    </div> 

    <div class="col-md-6">
        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'slogan')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'bussiness_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'logging')->checkbox() ?>
        <?= $form->field($model, 'status')->checkbox() ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>