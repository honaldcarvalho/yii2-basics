<?php

use app\models\NotificationMessage;
use app\models\User;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Notification */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="notification-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'description')->textInput() ?>

    <?= $form->field($model, 'notification_message_id')->widget(\kartik\select2\Select2::classname(), [
                                'data' => yii\helpers\ArrayHelper::map(NotificationMessage::find()
                                ->asArray()->all(),'id','description'),
                                'options' => ['multiple' => false, 'placeholder' => Yii::t('app','Select Message')],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label('Message');
                        ?>

    <?= $form->field($model, 'user_id')->widget(\kartik\select2\Select2::classname(), [
                                'data' => yii\helpers\ArrayHelper::map(User::find()
                                ->select('id,CONCAT_WS( " | ", `fullname`, `email`) AS `description`')
                                ->asArray()->all(),'id','description'),
                                'options' => ['multiple' => true, 'placeholder' => Yii::t('app','Select Users')],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])->label('Users');
                        ?>

    <?= $form->field($model, 'send_email')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
