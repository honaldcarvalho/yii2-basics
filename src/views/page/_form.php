<?php

use weebz\yii2basics\models\Language;
use weebz\yii2basics\models\Section;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\tinymce\TinyMce;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\Page $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="page-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'section_id')->dropDownList(
        yii\helpers\ArrayHelper::map(Section::find()->all(), 'id', 'name'), 
        ['prompt' => '-- selecione uma secção --']) ?>

    <?= $form->field($model, 'language_id')->dropDownList(
        yii\helpers\ArrayHelper::map(Language::find()->all(), 'id', 'name'), 
        ['prompt' => '-- selecione uma lingua --']) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->widget(TinyMce::className(), [
        'options' => ['rows' => 20],
        'language' => 'pt_br',
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

    <?= $form->field($model, 'custom_css')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'custom_js')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'keywords')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
