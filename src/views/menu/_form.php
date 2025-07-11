<?php

use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\models\Menu;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Menu */
/* @var $form yii\bootstrap5\ActiveForm */

$assetsDir = ControllerCommon::getAssetsDir();
$script = <<< JS

async function populateDropdown() {

    const response = await fetch('{$assetsDir}/plugins/fontawesome-free/list.json');
    const iconList = await response.json();

    const dropdown = document.getElementById('servico-icon');

    iconList.forEach(icon => {
        $('#servico-icon').append(`<option data-icon="\${icon}" value="\${icon}">\${icon}</option>`);
    });
}

function iformat(icon) {
    var originalOption = icon.element;
    return $('<span><i class="' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
}

populateDropdown().then(iconsArray => {

    $('#servico-icon').select2({
        width: "100%",
        templateSelection: iformat,
        templateResult: iformat,
        escapeMarkup: function(m) {
            return m;
        }
    });

    $('#servico-icon').val("{$model->icon}").trigger("change");
});
JS;

$this->registerJs($script);

?>

<div class="menu-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'menu_id')->widget(\kartik\select2\Select2::classname(), [
                                'data' => yii\helpers\ArrayHelper::map(Menu::find()
                                ->asArray()->all(),'id','label'),
                                'options' => ['multiple' => false, 'placeholder' => Yii::t('app','Select Menu')],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'width'=>'100%',
                                ],
                            ])->label('Menu');
                        ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icon')->dropDownList([], ['prompt' => '-- Selecione um Icone --']) ?>
    
    <?= $form->field($model, 'icon_style')->textInput(['maxlength' => true,'value'=>$model->isNewRecord ? 'fas' : $model->path]) ?>

    <?= $form->field($model, 'visible')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true,'value'=>$model->isNewRecord ? '#' : $model->url]) ?>

    <?= $form->field($model, 'path')->textInput(['maxlength' => true,'value'=>$model->isNewRecord ? 'app' : $model->path]) ?>

    <?= $form->field($model, 'order')->input('number') ?>

    <?= $form->field($model, 'active')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
