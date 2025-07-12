<?php

use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model weebz\yii2basics\models\Rule */

$this->title = Yii::t('app', 'Create Rule');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$js = <<<JS
$('#rule-controller').on('change', function () {
    let controller = $(this).val();
    $.post('/rule/get-actions', {controller: controller}, function (res) {
        if (res.success) {
            let actionSelect = $('#action-select');
            actionSelect.empty();
            for (let action of res.actions) {
                let option = new Option(action, action, false, false);
                actionSelect.append(option);
            }
            actionSelect.trigger('change');
        }
    });
});
JS;
$this->registerJs($js);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h1><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'controller')->widget(Select2::class, [
                        'data' => $controllers,
                        'options' => ['placeholder' => 'Selecione um controller...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ]
                    ]) ?>

                    <?= $form->field($model, 'actions')->widget(Select2::class, [
                        'data' => [],
                        'options' => [
                            'placeholder' => 'Selecione as actions...',
                            'multiple' => true,
                            'id' => 'action-select'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'tags' => false,
                        ]
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Salvar', ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>