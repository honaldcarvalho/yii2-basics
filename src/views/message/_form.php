<?php

use weebz\yii2basics\models\Language;
use weebz\yii2basics\models\SourceMessage;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Message */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="message-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= Html::label(Yii::t('app','Souce Message'), 'message-id')?>
        <?= \kartik\select2\Select2::widget([
            'model' => $model,
            'value' => 0,
            'attribute' => 'id',
            'data' => yii\helpers\ArrayHelper::map(SourceMessage::find()
            ->select('id,CONCAT_WS( " | ", `category`, `message`) AS `description`')
            ->asArray()->all(),'id','description'),
            'options' => ['placeholder' => Yii::t('backedn','-- SELECT MESSAGE -- ')],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]); ?>

    <?= $form->field($model, 'language')->dropdownList(yii\helpers\ArrayHelper::map(Language::find()->asArray()->all(), 'code', 'name')) ?>

    <?= $form->field($model, 'translation')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
