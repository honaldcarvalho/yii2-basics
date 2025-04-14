<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\components\gridview\ActionColumn;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;

class AppendModelFixed extends \yii\bootstrap5\Widget
{
    public $dataProvider;
    public $title = '';
    public $controller = null;
    public $attactClass;
    public $attactModel;
    public $childModel;
    public $callBack = '';
    public $childField;
    public $path = 'app';
    public $template = '{status}{view}{edit}{remove}';
    public $fields;
    public $showFields;
    public $order = false;
    public $orderField = 'order';
    public $orderModel = null;

    private $uniqueId;

    public function init(): void
    {
        parent::init();
        $this->uniqueId = uniqid($this->controller . '_');
    }

    public function run()
    {
        $columns = [['class' => 'yii\grid\CheckboxColumn']];

        $lower = $this->controller;
        $removeUrl = "/{$this->controller}/remove-model?modelClass={$this->attactModel}";
        $getUrl = "/{$this->controller}/get-model?modelClass={$this->attactModel}";
        $saveUrl = "/{$this->controller}/save-model?modelClass={$this->attactModel}";
        $statusUrl = "/{$this->controller}/status-model?modelClass={$this->attactModel}";

        $columns = array_merge($columns, $this->showFields);

        array_push($columns, [
            'class' => ActionColumn::class,
            'headerOptions' => ['style' => 'width:10%'],
            'template' => $this->template,
            'path' => $this->path,
            'controller' => $this->controller,
            'order' => $this->order,
            'orderField' => $this->orderField,
            'orderModel' => $this->orderModel,
        ]);

        $modalId = "save-{$lower}-{$this->uniqueId}";
        $gridId = "grid-{$lower}-{$this->uniqueId}";
        $formId = "form-{$lower}-{$this->uniqueId}";

        $script = <<< JS
            $(document).ready(function() {
                var modal = new bootstrap.Modal(document.getElementById('$modalId'));

                function saveModel() {
                    $('#overlay-form-$modalId').show();
                    var formData = $("#$formId").serialize();

                    $.ajax({
                        type: "POST",
                        url: "{$saveUrl}",
                        data: formData,
                    }).done(function(response) {       
                        if(response.success) {
                            toastr.success("Salvo com sucesso!");
                            modal.hide();
                            $.pjax.reload({container: "#$gridId", async: false});
                        } else {
                            toastr.error("Erro ao salvar!");
                        }
                    }).fail(function () {
                        toastr.error("Erro ao salvar!");
                    }).always(function () {
                        $('#overlay-form-$modalId').hide();
                    });
                }

                $("#btn-save-$modalId").click(saveModel);
            });
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        echo Html::button('<i class="fas fa-plus-square"></i> Novo', [
            'class' => 'btn btn-success',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => "#$modalId",
        ]);

        echo <<<HTML
        <div class="modal fade" id="$modalId" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{$this->title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="overlay-form-$modalId" class="overlay" style="display:none;">
                            <i class="fas fa-sync fa-spin"></i>
                        </div>
        HTML;

        $form = ActiveForm::begin(['id' => $formId]);
        $model = new $this->attactClass();
        echo $form->field($model, 'id')->hiddenInput()->label(false);

        foreach ($this->fields as $field) {
            $options = ['id' => "{$lower}-{$field['name']}-{$this->uniqueId}"];
            if ($field['type'] === 'text') {
                echo $form->field($model, $field['name'])->textInput($options);
            } elseif ($field['type'] === 'number') {
                echo $form->field($model, $field['name'])->input('number', $options);
            } elseif ($field['type'] === 'hidden') {
                echo $form->field($model, $field['name'])->hiddenInput($options)->label(false);
            } elseif ($field['type'] === 'checkbox') {
                echo $form->field($model, $field['name'])->checkbox($options);
            } elseif ($field['type'] === 'dropdown') {
                echo $form->field($model, $field['name'])->dropDownList($field['value'] ?? [], ['class' => 'form-control']);
            }
        }

        ActiveForm::end();

        echo <<<HTML
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="btn-save-$modalId" type="button" class="btn btn-success">
                            <i class="fas fa-plus-circle"></i> Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        HTML;

        Pjax::begin(['id' => $gridId]);
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $this->dataProvider,
            'columns' => $columns,
        ]);
        Pjax::end();
    }
}
