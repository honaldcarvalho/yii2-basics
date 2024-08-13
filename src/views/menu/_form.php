<?php

use weebz\yii2basics\modules\common\models\Menu;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\modules\common\models\Menu */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="menu-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'menu_id')->widget(\kartik\select2\Select2::classname(), [
                                'data' => yii\helpers\ArrayHelper::map(Menu::find()
                                ->asArray()->all(),'id','label'),
                                'options' => ['multiple' => false, 'placeholder' => Yii::t('app','Select Menu')],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label('Menu');
                        ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icon')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'icon_style')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'visible')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true,'value'=>$model->isNewRecord ? '#' : $model->url]) ?>

    <?= $form->field($model, 'order')->input('number') ?>

    <?= $form->field($model, 'active')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
