<?php

use weebz\yii2basics\models\NotificationMessage;
use weebz\yii2basics\models\User;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Notification */
/* @var $form yii\bootstrap5\ActiveForm */

$notification_message_id = null;

if(!$model->isNewRecord){
    $notification_message_id = $model->notification_message_id;
}else{
    $users = yii\helpers\ArrayHelper::map(User::find()
    ->select('id,CONCAT_WS( " | ", `fullname`, `email`) AS `description`')
    ->asArray()->all(),'id','description');
}

$script = <<< JS

    var elements_selected = [];
    var elements_diff = [];
    var values = [];

    $(function(){
        $('#notification-notification_message_id').select2({width:'100%',allowClear:true,placeholder:'-- Select one Controller --'});
        $('#notification-user_id').select2({width:'100%',allowClear:true,placeholder:'',multiple:true});
    });
JS;
$this->registerJs ($script, $this::POS_LOAD);

?>

<div class="notification-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'description')->textInput() ?>

    <?= $form->field($model, 'notification_message_id')->dropDownList(yii\helpers\ArrayHelper::map(NotificationMessage::find()
                                ->asArray()->all(),'id','description'), ['multiple'=>false,'prompt' => Yii::t('app','Select Notification'),
                                'value'=>$notification_message_id]);
                        ?>

    <?= $form->field($model, 'user_id')->dropDownList($users, ['multiple'=>true,'prompt' => Yii::t('app','Select User(s)')]);
                        ?>

    <?= $form->field($model, 'send_email')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
