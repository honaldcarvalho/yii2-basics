<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace weebz\yii2basics\components\gridview;

use Yii;
use yii\helpers\Html;
use weebz\yii2basics\controllers\AuthController;

class ActionColumn extends \yii\grid\ActionColumn
{
    public $template = '{view} {update} {delete}';
    public $verGroup = true;
    public $controller = null;
    public $path = null;
    public $model = null;
    public $order = false;
    public $orderField = 'order';
    public $orderModel = null;
    public $modelClass = null;
    public $gridId = null;
    /**
     * Initializes the default button rendering callbacks.
     */
    public function init(): void
    {
        $this->contentOptions['class'] = 'action-column';
        if($this->grid->filterModel  !== null){
            $class_path = get_class($this->grid->filterModel);
            $class_path_parts = explode('\\',$class_path);
            $class_name = end($class_path_parts);
            $this->model = $class_name;
            $this->modelClass = $this->grid->filterModel;
        }
        
        if($this->gridId === null){
            $this->gridId = "#grid-{$this->controller}";
        }
        
        $this->grid->summaryOptions = ['class' => 'summary mb-2'];
        $this->grid->pager = ['class' => 'yii\bootstrap5\LinkPager'];
        parent::init();
    }

    protected function registerScript()
    {   
        if($this->controller == null)     
            $this->controller = Yii::$app->controller->id;
        
        $order = 0;
        if($this->order){
            $order = 1;
        }

        $script = <<< JS

            function clearForms()
            {
                $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
                $('#btn-add-translate').prop('disabled',false);
                $('select').val(null).trigger('change');
                return true;
            }   

            function get{$this->model}(e) {

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
                        toastr.error("Error on load {$this->controller}!");
                        return false;
                    } else {
                        Object.entries(response).forEach(([key, value]) => {
                            var el = $(`#{$this->controller}-\${key}`);
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
                        modal_{$this->model}.show();
                    }
                }).fail(function (response) {
                    toastr.error("Error on remove {$this->controller}!");
                }).always(function (response) {
                    object.removeClass('fas fa-sync fa-spin');
                    object.attr('class',old_class);
                });
            }

            function callAction(e,id,action){
                let items  = [];
                let i = 0;
                let el = $(e);
                let old_class = el.children("i").attr('class');
                let object = el.children("i");

                el.prop('disabled',true);
                object.removeClass(old_class);
                object.addClass('fas fa-sync fa-spin');

                $('#overlay-{$this->controller}').show();
                $( "{$this->gridId} .table tbody tr" ).each(function( index ) {
                    items[items.length] = $( this ).attr("data-key");
                });
                $.ajax({
                    method: "POST",
                    url: `/{$this->controller}/\${action}?id=\${id}`
                }).done(function(response) {        
                    toastr.success("Success!");  
                    $.pjax.reload({container: "{$this->gridId}", async: false}); 
                    return false;
                }).fail(function (response) {
                    toastr.error("Fail!");
                }).always(function (response) {
                    $('#overlay-{$this->controller}').hide();
                    el.prop('disabled',false);
                    object.removeClass('fas fa-sync fa-spin');
                    object.attr('class',old_class);
                });
            }

            $(function(){

                if($order == 1){
                    setSortable();
                }
                $(document).on('pjax:start', function() {
                    $('#overlay-{$this->controller}').show();
                });
                $(document).on('pjax:complete', function() {
                    $('#overlay-{$this->controller}').hide();
                });

            });
        JS;

        $script_order = <<< JS

            function updateOrder(){
                let items  = [];
                let i = 0;

                $('#overlay-{$this->controller}').show();
                $( "{$this->gridId} .table tbody tr" ).each(function( index ) {
                    items[items.length] = $( this ).attr("data-key");
                });

                $.ajax({
                    method: "POST",
                    url: '/{$this->controller}/order-model',
                    data: {'items':items,'field':'{$this->orderField}','modelClass':'$this->orderModel'}
                }).done(function(response) {        
                    toastr.success("atualizado");  
                    $.pjax.reload({container: "{$this->gridId}", async: false}); 
                    setSortable();
                }).fail(function (response) {
                    toastr.error("Error ao atualizar a ordem. Recarregue a pagina");
                }).always(function (response) {
                    $('#overlay-{$this->controller}').hide();
                });

            }

            function setSortable(){
                jQuery("{$this->gridId} .table tbody").sortable({
                    update: function(event, ui) {
                        updateOrder();
                    }
                });
            }
        
        JS;

        $view = Yii::$app->view;

        if($this->order)
            $view->registerJs($script_order, $view::POS_END);
        $view->registerJs($script, $view::POS_END);

    }

    protected function initDefaultButtons()
    {
        $this->initDefaultButton('status', 'status');
        $this->initDefaultButton('clone', 'fa-clone');
        $this->initDefaultButton('view', 'fa-eye');
        $this->initDefaultButton('edit', 'fa-pencil-alt');
        $this->initDefaultButton('update', 'fa-pencil-alt');
        $this->initDefaultButton('remove', 'fa-trash');
        $this->initDefaultButton('delete', 'fa-trash', [
            'data-confirm' => Yii::t('yii', 'Você tem certeza que quer remover esse item?'),
            'data-method' => 'post',
        ]);
        if(str_contains($this->template, 'status') || str_contains($this->template, 'remove') || str_contains($this->template, 'status')){
            $this->registerScript();
        }

    }

    public function renderDataCellContent($model, $key, $index)
    {
        return Html::tag('div', parent::renderDataCellContent($model, $key, $index), ['class' => 'btn-group']);
    }

    /**
     * Initializes the default button rendering callback for single button.
     * @param string $name Button name as it's written in template
     * @param string $iconName The part of Bootstrap glyphicon class that makes it unique
     * @param array $additionalOptions Array of additional options
     * @since 2.0.11
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {

        if($this->controller === null) {
            $controller_parts = explode('\\',get_class(Yii::$app->controller));
            if($this->path === null){
                if(count($controller_parts) == 4)
                    $this->path = "{$controller_parts[0]}/{$controller_parts[2]}";
                else
                    $this->path = "{$controller_parts[0]}";
            }
            $controller_parts = explode('Controller',end($controller_parts));
            $this->controller = strtolower($controller_parts[0]);
            if(($tranformed = AuthController::addSlashUpperLower($controller_parts[0])) != false){
                $this->controller = $tranformed;
            }

        }
        
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {

                $options = array_merge([
                    'title' => Yii::t('yii',ucfirst($name)),
                    'aria-label' => Yii::t('yii',ucfirst($name)),
                    'data-pjax' => '0',
                    'class'=>'btn btn-outline-secondary',
                ], $additionalOptions, $this->buttonOptions);

                $icon = Html::tag('i', '', ['class' => "fas $iconName"]);

                switch ($name) {
                    case 'view':
                        $link = Html::a($icon, $url, $options);
                        break;
                    case 'update':
                        $link = Html::a($icon, $url, $options);
                        break;
                    case 'delete':
                        $link = Html::a($icon, $url, $options);
                        break;
                    case 'clone':
                        $icon = Html::tag('i', '', ['class' => "fas fa-clone"]);
                        $link = Html::a($icon,  "javascript:;", ['data-link'=> "/{$this->controller}/clone?id=$model->id", 'class'=>'btn btn-default']);
                        break;
                    case 'edit':
                        $link = Html::a($icon,  "javascript:;", ['data-link'=> "/{$this->controller}/get-model?modelClass={$this->model}&id=$model->id",'onclick'=>"get{$this->model}(this);", 'class'=>'btn btn-default']);
                        break;
                    case 'remove':
                        $link = Html::a($icon,  "javascript:;", ['onclick'=>"callAction(this,{$model->id},'remove')", 'class'=>'btn btn-default']);
                        break;
                    case 'status':
                        $link = Html::a('<i class="fas fa-toggle-'.(!$model->status ? 'off' : 'on').'"></i>',  "javascript:;", ['onclick'=>"callAction(this,{$model->id},'status')", 'class'=>'btn btn-default']);
                        break;
                    default:
                        $title = ucfirst($name);
                }

                if($this->verGroup && !AuthController::isAdmin()){
                    if(AuthController::verAuthorization($this->controller,$name,$model,$this->path)){
                        return $link;
                    }
                }else if(!AuthController::isAdmin()){
                    if(AuthController::verAuthorization($this->controller,$name,$model,$this->path)){
                        return $link;
                    }
                }else{
                    return $link;
                }
                return '';

            };
        }

    }
}