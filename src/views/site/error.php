<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = \Yii::t('app',$name);
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="error-page">
    <div class="error-content" style="margin-left: auto;">
        <h3><i class="fas fa-exclamation-triangle text-danger"></i> <?= \Yii::t('app',$name); ?></h3>

        <p>
            <?= \Yii::t('app','The above error occurred while the Web server was processing your request. Please contact us if you think this is a server error. Thank you. Meanwhile, you may '); ?>
            <b><?= Html::a( \Yii::t('app','return to dashboard'), Yii::$app->homeUrl,['class'=>"active"]); ?></b>.
        </p>
    </div>
</div>

