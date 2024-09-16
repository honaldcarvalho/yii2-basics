<?php

use weebz\yii2basics\widgets\TinyMCE;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\NotificationMessage */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="notification-message-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList([ 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Danger', 'default' => 'Default', 'info' => 'Info', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'message')->widget(TinyMCE::class, [
        'options' => ['rows' => 20],
        'language' => \Yii::$app->language,
        //'language' => 'pt_br',
        'clientOptions' => [
            'plugins' => [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
                'table', 'emoticons', 'template', 'help'            ],
                        'toolbar' => "undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
                'forecolor backcolor emoticons"
            ]
        ]);?>


    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
