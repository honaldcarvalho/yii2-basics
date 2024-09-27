<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\components\gridview\ActionColumn;

use Yii;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;

class AppendModel extends \yii\bootstrap5\Widget
{

    public $dataProvider;
    public $title = '';
    public $actionUrl = null;
    public $attactClass;
    public $attactModel;
    public $childModel;
    public $childField;
    public $fields;
    public $showFields;
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $columns = [['class' => 'yii\grid\CheckboxColumn']];
        $lower = strtolower($this->attactModel);
        if(isset($this->actionUrl) == null)
            $this->actionUrl = "/{$lower}/add-{$lower}";

        $columns = array_merge($columns,$this->showFields);
        array_push($columns,[
            'class'=> ActionColumn::class,
            'headerOptions' => ['style' => 'width:10%'],
            'template' => '{view}{delete}',
            'path' => 'app',
            'controller' => 'file',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return
                        Html::button(
                            '<i class="fas fa-trash"></i>',
                            ['onclick' => 'removeFiles(this)', 'class' => 'btn btn-outline-secondary', "data-id" => $model->id, "data-toggle" => "tooltip", "data-placement" => "top", "title" => \Yii::t('app', 'Remove')]
                        );
                },
            ]
        ]);

        $script = <<< JS
            let modal = null;

            $(function(){
                modal = new bootstrap.Modal(document.getElementById('add-{$lower}'), {
                    keyboard: true
                });
            });

            function add{$this->attactModel}(){
                $('#overlay-form-{$lower}').show();
                var formData = $("#form-{$lower}").serialize();
                console.log(formData);
                $.ajax({
                    type: "POST",
                    url: "{$this->actionUrl}",
                    data: formData,
                }).done(function(response) {       
                    if(response.success) {
                        toastr.success("Added!");
                        modal.hide();
                    } else {
                        toastr.error("Error on add!");
                    }
                    $.pjax.reload({container: "#list-{$lower}-grid", async: true});
                    //clearForms();
                }).fail(function (response) {
                    toastr.error("Error on add!");
                }).always(function (response) {
                    $('#overlay-form-{$lower}').hide();
                });
            }

            $("#btn-add-{$lower}").click(function(){
                add{$this->attactModel}();
            });

            $(function(){
                $(document).on('pjax:start', function() {
                    $('#overlay-{$lower}').show();
                });
                $(document).on('pjax:complete', function() {
                    $('#overlay-{$lower}').hide();
                });
                Fancybox.bind("[data-fancybox]");
            });

        JS;

        \Yii::$app->view->registerJs($script,View::POS_END);
        $field_str = '';

        $button = Html::a('<i class="fas fa-plus-square"></i> Novo', 'javascript:modal.show();', ['class' => 'btn btn-success','id'=>'btn-show-{$lower}']);
        $button_save = Yii::t('app', "Save");
        $button_cancel = Yii::t('app', 'Cancel');
        $begin = <<< HTML
            <!-- Modal -->
            <div class="modal fade" id="add-{$lower}" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">{$this->title}</h5>
                            <button type="button" class="btn-close" onclick="javascript:modal.hide();" aria-label="Close"></button>
                        </div>
                        <div id="overlay-form-{$lower}" class="overlay" style="height: 100%;position: absolute;width: 100%;z-index: 3000;display:none;top:0;left:0;">
                            <div class="fa-3x">
                                <i class="fas fa-sync fa-spin"></i>
                            </div>
                        </div>
                        <div class="modal-body" style="font-size:1em;">
        HTML;
        
        $end = <<< HTML
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="javascript:modal.hide();"> {$button_cancel} </button>
                            <button id="btn-add-{$lower}" onclick="add{$this->attactModel}" type="button" class="btn btn-success"><i class="fas fa-plus-circle mr-2 icon"></i> {$button_save} </button>
                        </div>
                    </div>
                </div>
            </div>
        HTML;

        echo $begin;
        $form = ActiveForm::begin(['id'=>"form-{$lower}"]); 
        $model = new $this->attactClass();

        foreach ($this->fields as $key => $field) {
            $field_str .= '<div class="col-md-12">';

            if($field['type'] == 'text')
                $field_str .=  $form->field($model, $field['name'])->textInput(['maxlength' => true]);
            else if($field['type'] == 'hidden')
                $field_str .=  $form->field($model, $field['name'])->hiddenInput(['maxlength' => true,'value'=> $field['value']])->label(false);
            else if($field['type'] == 'checkbox')
                $field_str .=  $form->field($model, $field['name'])->checkbox() ;
            else if($field['type'] == 'dropdown')
                $field_str .=  $form->field($model, $model, $field['name'])->widget(\kartik\select2\Select2::classname(), [
                    'data' =>  $field['value'],
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('*','Select')],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);

            $field_str .= '</div>';
        }
        echo $field_str;
        ActiveForm::end(); 
        echo $end;

        $gridView = GridView::widget([
                        'id' => 'grid-files',
                        'dataProvider' =>  $this->dataProvider,
                        'columns' => $columns
                    ]);
    
        $head = <<< HTML
            <div class="card" id="list-{$lower}">
    
                <div class="card-header">
                    <h3 class="card-title">List {$this->title}</h3>
                </div>
    
                <div class="card-body">
                    <p>
                        {$button}
                    </p>
                    <div class="row">
                        <div class="col-md-12">
    
                            <div id='overlay-{$lower}' class='overlay' style='display:none;height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                                <div class='d-flex align-items-center'>
                                    <strong> <?= Yii::t('app', 'Loading...') ?></strong>
                                    <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                                </div>
                            </div>
    
        HTML;
    
        $footer = <<< HTML
                        </div>
                        <!--.col-md-12-->
                    </div>
                    <!--.row-->
                </div>
    
            </div>
        HTML;


        echo $head;
        Pjax::begin(['id' => "list-{$lower}-grid"]);
          echo $gridView;
        Pjax::end();
        echo $footer;
    }

}
