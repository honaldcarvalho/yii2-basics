<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\components\gridview\ActionColumn;

use Yii;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;
/**
 Example:
             <?= AppendModel::widget([
                'title'=>'Instituições',
                'attactModel'=>'Instituicao',
                'controller'=>'instituicao',
                'template' => '{status} {view} {remove}',
                'order'=>true,
                'reloadGrid'=>null,
                'orderField'=>'ordem',
                'orderModel'=>'Instituicao',
                'attactClass'=>'app\\models\\Instituicao',
                'dataProvider' => new \yii\data\ActiveDataProvider([
                    'query' => $model->getInstituicoes(),
                ]),
                'showFields'=>[
                    'id',
                    'titulo',
                    'nome',
                    'ordem',
                    'status:boolean'
                ],
                'fields'=>
                [
                    [
                        'name'=>'instituicao_secao_id',
                        'type'=>'hidden',
                        'value'=>$model->id
                    ],
                    [
                        'name'=>'titulo',
                        'type'=>'text'
                    ],
                    [
                        'name'=>'nome',
                        'type'=>'text'
                    ],
                    [
                        'name'=>'status',
                        'type'=>'checkbox'
                    ],
                ]
            ]); ?>
 */
class AppendModel extends \yii\bootstrap5\Widget
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

    public $removeUrl;
    public $getUrl;
    public $saveUrl;
    public $statusUrl;
    public $random;
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $columns = [['class' => 'yii\grid\CheckboxColumn']];

        $lower = $this->controller;
        $this->random = rand(10000,99999);
        $this->removeUrl = "/{$this->controller}/remove-model?modelClass={$this->attactModel}";
        $this->getUrl = "/{$this->controller}/get-model?modelClass={$this->attactModel}";
        $this->saveUrl = "/{$this->controller}/save-model?modelClass={$this->attactModel}";
        $this->statusUrl = "/{$this->controller}/status-model?modelClass={$this->attactModel}";
        $form_name = strtolower($this->attactModel);
        $columns = array_merge($columns,$this->showFields);

        array_push($columns,[
            'class'=> ActionColumn::class,
            'headerOptions' => ['style' => 'width:10%'],
            'template' => $this->template,
            'path' =>  $this->path,
            'controller' => $this->controller,
            'order'=>$this->order,
            'orderField'=> $this->orderField,
            'orderModel'=>$this->orderModel,
            'buttons' => [
                'status' => function ($url, $model, $key) {
                    return Html::a('<i class="fas fa-toggle-'.(!$model->status ? 'off' : 'on').'"></i>','javascript:;',
                     [
                        'onclick'=>"status{$this->attactModel}(this);", 
                        'data-link'=> "{$this->statusUrl}&id=$model->id",
                        'class'=>'btn btn-outline-secondary status',"data-toggle"=>"tooltip","data-placement"=>"top", 
                        "title"=>\Yii::t('*',"Change Status {$this->attactModel}")
                    ]);
                }, 
                'remove' => function ($url, $model, $key) {
                    return Html::a('<i class="fas fa-trash"></i>','javascript:;',
                     [
                        'onclick'=>"remove{$this->attactModel}(this);", 
                        'data-link'=> "{$this->removeUrl}&id=$model->id",
                        'class'=>'btn btn-outline-secondary remove',"data-toggle"=>"tooltip","data-placement"=>"top", 
                        "title"=>\Yii::t('*',"Remove {$this->attactModel}")
                    ]);
                }, 
                'edit' => function ($url, $model, $key) {
                    return Html::a('<i class="fas fa-pen"></i>','javascript:;',
                     [
                        'onclick'=>"get{$this->attactModel}(this);", 
                        'data-link'=> "{$this->getUrl}&id=$model->id",
                        'class'=>'btn btn-outline-secondary edit',"data-toggle"=>"tooltip","data-placement"=>"top", 
                        "title"=>\Yii::t('*',"Edit {$this->attactModel}")
                    ]);
                }, 
            ]
        ]);

        $script = <<< JS
            let modal_{$this->attactModel} = null;

            $(function(){
                modal_{$this->attactModel} = new bootstrap.Modal(document.getElementById('save-{$lower}'), {
                    keyboard: true
                });
                $('.dropdown').select2({width:'100%',allowClear:true,placeholder:'Selecione',dropdownParent: $('#')});
            });

            function save{$this->attactModel}(){
                $('#overlay-form-{$lower}').show();
                var formData = $("#form-{$lower}").serialize();
                console.log(formData);
                $.ajax({
                    type: "POST",
                    url: "{$this->saveUrl}",
                    data: formData,
                }).done(function(response) {       
                    if(response.success) {
                        toastr.success("Save!");
                        modal_{$this->attactModel}.hide();
                        $.pjax.reload({container: "#list-{$lower}-grid", async: false});
                        {$this->callBack}
                    } else {
                        toastr.error("Error on save!");
                    }
                }).fail(function (response) {
                    toastr.error("Error on add!");
                }).always(function (response) {
                    $('#overlay-form-{$lower}').hide();
                });
            }

            function get{$this->attactModel}(e) {

                let el = $(e);

                object = el.children("i");
                let old_class = el.children("i").attr('class');
                object.removeClass(old_class);
                object.addClass('fas fa-sync fa-spin');

                $.ajax({
                    type: "POST",
                    url: el.data('link'),
                }).done(function(response) {     
                    if(response == null){
                        toastr.error("Error on load {$lower}!");
                        return false;
                    } else {
                        Object.entries(response).forEach(([key, value]) => {
                            var el = $(`#{$lower}-\${key}`);
                            if(el.attr('type') == 'checkbox') {
                                if (value === 1) {
                                    el.prop('checked', true);
                                } else {
                                    el.prop('checked', false);
                                }
                            } else if(el.attr('type') == 'select') {
                                el.val(value); // Select the option with a value of '1'
                                el.trigger('change');
                            } else {
                                el.val(value);
                            }
                        });
                        modal_{$this->attactModel}.show();
                    }
                }).fail(function (response) {
                    toastr.error("Error on remove {$lower}!");
                }).always(function (response) {
                    object.removeClass('fas fa-sync fa-spin');
                    object.attr('class',old_class);
                });
            }

            function status{$this->attactModel}(e) {

                let el = $(e);

                object = el.children("i");
                let old_class = el.children("i").attr('class');
                object.removeClass(old_class);
                object.addClass('fas fa-sync fa-spin');

                $.ajax({
                    type: "POST",
                    url: el.data('link'),
                }).done(function(response) {     
                    if(response == null){
                        toastr.error("Error on load {$lower}!");
                        return false;
                    } else {
                        if(response.success) {
                            toastr.success("Status Changed!");
                            modal_{$this->attactModel}.hide();
                            $.pjax.reload({container: "#list-{$lower}-grid", async: false});
                            {$this->callBack}
                        } else {
                            toastr.error("Error on save!");
                        }
                    }
                }).fail(function (response) {
                    toastr.error("Error on remove {$lower}!");
                }).always(function (response) {
                    object.removeClass('fas fa-sync fa-spin');
                    object.attr('class',old_class);
                });
            }

            function remove{$this->attactModel}(e) {

                let el = $(e);
                if (confirm('You really can remove this {$lower}?')) {

                    object = el.children("i");
                    let old_class = el.children("i").attr('class');
                    object.removeClass(old_class);
                    object.addClass('fas fa-sync fa-spin');

                    $.ajax({
                        type: "POST",
                        url: el.data('link'),
                    }).done(function(response) {     
                        if(response == 0){
                            toastr.error("Error on remove {$lower}!");
                            return false;
                        }
                        toastr.success("Removed!");
                        $.pjax.reload({container: "#grid-{$lower}", async: false});
                    }).fail(function (response) {
                        toastr.error("Error on remove {$lower}!");
                    }).always(function (response) {
                        object.removeClass('fas fa-sync fa-spin');
                        object.attr('class',old_class);
                    });
                }
                return false;
            }

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

        $button = Html::a('<i class="fas fa-plus-square"></i> Novo', "javascript:modal_{$this->attactModel}.show();clearForms();", ['class' => 'btn btn-success','id'=>"btn-show-{$lower}"]);
        $button_save = Yii::t('app', "Save");
        $button_cancel = Yii::t('app', 'Cancel');
        $begin = <<< HTML
            <!-- Modal -->
            <div class="modal fade" id="" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">{$this->title}</h5>
                            <button type="button" class="btn-close" onclick="javascript:modal_{$this->attactModel}.hide();" aria-label="Close"></button>
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
                            <button type="button" class="btn btn-secondary" onclick="javascript:modal_{$this->attactModel}.hide();"> {$button_cancel} </button>
                            <button id="btn-save-{$lower}" onclick="save{$this->attactModel}()" type="button" class="btn btn-success"><i class="fas fa-plus-circle mr-2 icon"></i> {$button_save} </button>
                        </div>
                    </div>
                </div>
            </div>
        HTML;

        echo $begin;
        $form = ActiveForm::begin(['id'=>"form-{$lower}"]); 
        $model = new $this->attactClass();
        $field_str .=  $form->field($model, 'id')->hiddenInput(['id'=> "{$lower}-id", 'maxlength' => true])->label(false);

        foreach ($this->fields as $key => $field) {
            $field_str .= '<div class="col-md-12">';
            if($field['type'] == 'text')
                $field_str .= $form->field($model, $field['name'])->textInput(['id'=> "{$lower}-{$field['name']}", 'maxlength' => true,'value'=> $field['value'] ?? '']);
            else if($field['type'] == 'number')
                $field_str .=  $form->field($model, $field['name'])->input('number',['id'=> "{$lower}-{$field['name']}", 'maxlength' => true,'value'=> $field['value'] ?? '']);
            else if($field['type'] == 'hidden')
                $field_str .=  $form->field($model, $field['name'])->hiddenInput(['id'=> "{$lower}-{$field['name']}",'maxlength' => true,'value'=> $field['value'] ?? ''])->label(false);
            else if($field['type'] == 'checkbox')
                $field_str .=  $form->field($model, $field['name'])->checkbox(['id'=> "{$lower}-{$field['name']}",]) ;
            else if($field['type'] == 'dropdown'){
                $field_str .=  $form->field($model, $field['name'])->dropDownList($field['value'] ?? '',['class'=>'form-control dropdown']);
            }
            $field_str .= '</div>';
        }
        echo $field_str;
        ActiveForm::end(); 
        echo $end;

        $gridView = GridView::widget([
                        'id' => "grid-{$lower}",
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
                                    <strong> Loading... </strong>
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
