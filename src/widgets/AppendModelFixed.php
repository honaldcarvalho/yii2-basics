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
    public $template = '{status}{view}{edit}{remove}';
    public $fields;
    public $showFields;
    public $order = false;
    public $orderField = 'order';

    private $widgetId;

    public function init()
    {
        parent::init();
        $this->widgetId = uniqid('append_model_');
    }

    public function run()
    {
        $lower = strtolower($this->controller);
        $form_name = strtolower($this->attactModel);
        $gridId = "grid-{$this->widgetId}";
        $modalId = "modal-{$this->widgetId}";

        $removeUrl = "/{$this->controller}/remove-model?modelClass={$this->attactModel}";
        $getUrl = "/{$this->controller}/get-model?modelClass={$this->attactModel}";
        $saveUrl = "/{$this->controller}/save-model?modelClass={$this->attactModel}";

        $columns = array_merge(
            [['class' => 'yii\grid\CheckboxColumn']],
            $this->showFields,
            [[
                'class' => ActionColumn::class,
                'template' => $this->template,
                'controller' => $this->controller,
                'buttons' => [
                    'status' => fn($url, $model, $key) => Html::a('<i class="fas fa-toggle-'.(!$model->status ? 'off' : 'on').'"></i>', 'javascript:;', [
                        'class' => 'btn btn-outline-secondary status-btn',
                        'data-url' => "{$this->statusUrl}&id={$model->id}",
                    ]),
                    'remove' => fn($url, $model, $key) => Html::a('<i class="fas fa-trash"></i>', 'javascript:;', [
                        'class' => 'btn btn-outline-secondary remove-btn',
                        'data-url' => "{$removeUrl}&id={$model->id}",
                    ]),
                    'edit' => fn($url, $model, $key) => Html::a('<i class="fas fa-pen"></i>', 'javascript:;', [
                        'class' => 'btn btn-outline-secondary edit-btn',
                        'data-url' => "{$getUrl}&id={$model->id}",
                        'data-modal' => "#{$modalId}"
                    ]),
                ]
            ]]
        );

        $script = <<< JS
            $(document).on('click', '.edit-btn', function() {
                let modalId = $(this).data('modal');
                let url = $(this).data('url');

                $.get(url, function(response) {
                    if (response) {
                        for (let key in response) {
                            $("#{$modalId} [name*='[" + key + "]']").val(response[key]);
                        }
                        $(modalId).modal('show');
                    }
                });
            });

            $(document).on('click', '.remove-btn', function() {
                if (confirm('Você realmente deseja remover?')) {
                    $.post($(this).data('url'), function(response) {
                        if (response.success) {
                            $.pjax.reload({container: "#{$gridId}"});
                        }
                    });
                }
            });
        JS;
        Yii::$app->view->registerJs($script, View::POS_END);

        $form = ActiveForm::begin(['id' => "form-{$this->widgetId}"]);
        echo Html::beginTag('div', ['class' => 'modal fade', 'id' => $modalId]);
        echo Html::beginTag('div', ['class' => 'modal-dialog']);
        echo Html::beginTag('div', ['class' => 'modal-content']);
        echo Html::tag('div', Html::tag('h5', $this->title, ['class' => 'modal-title']), ['class' => 'modal-header']);
        echo Html::beginTag('div', ['class' => 'modal-body']);

        $model = new $this->attactClass();
        echo $form->field($model, 'id')->hiddenInput()->label(false);
        foreach ($this->fields as $field) {
            echo $form->field($model, $field['name'])->textInput();
        }

        echo Html::endTag('div'); // modal-body
        echo Html::tag('div', Html::button('Salvar', ['class' => 'btn btn-success', 'onclick' => "$('#form-{$this->widgetId}').submit();"]), ['class' => 'modal-footer']);
        echo Html::endTag('div'); // modal-content
        echo Html::endTag('div'); // modal-dialog
        echo Html::endTag('div'); // modal
        ActiveForm::end();

        echo Html::a('Adicionar', 'javascript:;', ['class' => 'btn btn-success', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#{$modalId}"]);

        Pjax::begin(['id' => $gridId]);
        echo GridView::widget(['dataProvider' => $this->dataProvider, 'columns' => $columns]);
        Pjax::end();
    }
}
