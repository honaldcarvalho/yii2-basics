<?php

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\Rule $model */
/** @var yii\widgets\ActiveForm $form */

use weebz\yii2basics\models\Group;;
use weebz\yii2basics\models\User;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

$actions_diff = [];
$actions_model = [];
$controller_actions = $model->getControllers();

foreach ($controller_actions['controllers'] as $key => $controller) {
    $controller_actions_opts[$key] = $key;
}

if(!$model->isNewRecord){
    
    $actions_controller = $controller_actions['controllers_actions'][strtoupper($model->path).":{$model->controller}"];
    $actions_model = explode(';',$model->actions);

    foreach($actions_controller as $action){
        if(!in_array($action, $actions_model)){
            $actions_diff[] = $action;
        }
    }

    $origins = explode(';',$model->origin);

    $model->origin = $origins;
}

$json = json_encode($controller_actions['controllers_actions']);
$selected = json_encode($actions_model);
$diff = json_encode($actions_diff);

$script = <<< JS

    var elements_selected = [];
    var elements_diff = [];
    var values = [];

    $('#multiselect').multiselect();

    $(function(){

        $('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
        });

        data     = JSON.parse('$json');
        selected = JSON.parse('$selected');
        diff     = JSON.parse('$diff');
        console.log(data);
        $('#add_origin').keyup(function( event ) {

            if (event.keyCode === 13) {
                 var newOption = new Option($('#add_origin').val(),$('#add_origin').val(), true, true);
                 $('#rule-origin').append(newOption).trigger('change');
                 //$('#rule-origin').val(null).trigger('change');
                 $('#add_origin').val('');
            }
        });
        
        $.each(selected, function(i, action ) {              
            var option = document.createElement("option"); 
            option.innerHTML = action;
            option.value = action;
            option.setAttribute("type", "checkbox");              
            elements_selected.push(option);             

        });
        
        if(elements_selected !== null){
            $("#multiselect_to").html();
            $("#multiselect_to").html(elements_selected);
        }    
        
        $.each(diff, function(i, action ) {              
            elements = [];  
            var option = document.createElement("option"); 
            option.innerHTML = action;
            option.value = action;
            option.setAttribute("type", "checkbox");              
            elements_diff.push(option);               

        });
        
        if(elements_diff !== null){
            $("#multiselect").html();
            $("#multiselect").html(elements_diff);
        }  
        
        $('#rule-controller').change(function(){
            elements = [];
            $(this).find(":selected").text();
            
            $.each(data, function(controller, actions ) {              
                
                if(controller == $('#rule-controller').find(":selected").text()){

                    $.each(actions, function(i, action ) {
                        var option = document.createElement("option"); 
                        option.innerHTML = action;
                        option.value = action;
                        option.setAttribute("type", "checkbox");              
                        elements.push(option);     
                    });
                    
                    if(elements !== null){
                        $("#multiselect").html();
                        $("#multiselect").html(elements);
                    }

                }
                
            });
            
        });
        $('#rule-controller').select2({width:'100%',allowClear:true,placeholder:'-- Select one Controller --'});
        $('#rule-origin').select2({width:'100%',allowClear:true,placeholder:''});
    });
JS;
$this->registerJs ($script, View::POS_LOAD);

?>

<div class="rule-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'group_id')->dropDownList(yii\helpers\ArrayHelper::map(Group::find()->asArray()->all(), 'id', 'name'), ['prompt' => '-- selecione um grupo --']) ?>

    <?= $form->field($model, 'user_id')->dropDownList(yii\helpers\ArrayHelper::map(User::find()->select('id,username')->asArray()->all(), 'id', 'username'), ['prompt' => '-- selecione um usuario --']) ?>
    
    <?php if($model->isNewRecord): ?>
        <?= $form->field($model, 'controller')->dropDownList($controller_actions_opts, ['multiple'=>false,'prompt' => '-- CONTROLLER --']) ?>
    <?php else: ?>
        <?= $form->field($model, 'controller')->textInput(['readonly'=>true]) ?>
    <?php endif; ?>

    <div id="actions" class="form-group">    
        <?= Html::label(Yii::t('app','Enter origin and digit enter:'), 'add_origin')?>
        <?= Html::textInput('add_origin','',['id'=>'add_origin','class'=>'form-control']) ?>
    </div>

    <?= $form->field($model, 'origin', ['enableClientValidation'=>false])->dropDownList([])->label('Origins');
    ?>

    <div id="actions" class="form-group">
        <div class="row">
        <div class="col-md-5">
            <select name="from[]" id="multiselect" class="form-control" size="8" multiple="multiple">

            </select>
        </div>
        <div class="col-md-2">
            <button type="button" id="multiselect_rightAll" class="btn btn-block"><i class="fas fa-forward"></i></button>
            <button type="button" id="multiselect_rightSelected" class="btn btn-block"><i class="fas fa-chevron-right"></i></button>
            <button type="button" id="multiselect_leftSelected" class="btn btn-block"><i class="fas fa-chevron-left"></i></i></button>
            <button type="button" id="multiselect_leftAll" class="btn btn-block"><i class="fas fa-backward"></i></button>
        </div>
        <div class="col-md-5">
            <select name="to[]" id="multiselect_to" class="form-control" size="8" multiple="multiple">

            </select>
        </div>
        </div>
    </div>

    <?= $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>'.Yii::t('app','Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>