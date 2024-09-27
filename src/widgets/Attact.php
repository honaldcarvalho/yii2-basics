<?php

namespace weebz\yii2basics\widgets;

use Yii;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * @author Honald Carvalho da Silva <honalcarvalho@gmail.com>
 */
class Attact extends \yii\bootstrap5\Widget
{
    /**
     * @var array the options for rendering the close button tag.
     * Array will be passed to [[\yii\bootstrap\Alert::closeButton]].
     */
    public $attact_model;
    public $child;
    public $child_field;
    public $child_field_text;
    public $root_field;
    public $root_field_value;
    public $dataProvider;
    public $show_fields = [];
    
    public function init() {}
  
    /**$
     * {@inheritdoc}
     */
    public function run()
    {
  
        $script = <<< JS

            $lower = strtolower($this->attact_model);
            function removeFiles(e) {
        
                let el = $(e);
                let ids = $('#grid-files').yiiGridView('getSelectedRows');
        
                if (confirm('You really can remove this file(s)?')) {
        
                    let keys = [];
                    keys = keys.concat(ids);
        
                    if(el.attr('id') !== 'remove-files'){
                        keys.push(el.data('id'));
                    }
        
                    if(keys.length <= 0){
                        alert("No files selected!");
                        return false;
                    }
        
                    let old_class = el.children("i").attr('class');
                    el.prop('disabled',true);
                    object = el.children("i");
                    object.removeClass(old_class);
                    object.addClass('fas fa-sync fa-spin');
        
                    $('#overlay-files').show();
        
                    $.ajax({
                        type: "POST",
                        url: "/rest/storage/remove-files",
                        data: {keys:keys},
                        headers: {
                            'Authorization': `Bearer {$token}`
                        },
                    }).done(function(response) {     
                        
                        if(response.length > 0){
                            $.each(response, function (indexInArray, valueOfElement) { 
                                if(valueOfElement.success){
                                    toastr.success(valueOfElement.message);
                                }else{
                                    toastr.error(valueOfElement.message);
                                }
                            });
                        }
                        $.pjax.reload({container: "#list-files-grid", async: false});
                        return false;
                    }).fail(function (response) {
                        toastr.error("Error on remove files!");
                    }).always(function (response) {
                        el.prop('disabled',false);
                        object.removeClass('fas fa-sync fa-spin');
                        object.attr('class',old_class);
                    });
        
                }
                return false;
            }
            
            function add{$attact_model}(){
                $('#overlay-{$lower}').show();
                var formData = $("#form-translate").serialize();

                $.ajax({
                    type: "POST",
                    url: "/{$lower}/add-{$lower}",
                    data: formData,
                }).done(function(response) {        
                    toastr.success("Added!");
                    $.pjax.reload({container: "#list-{$lower}-grid", async: true});
                    modal.hide();
                    clearForms();
                }).fail(function (response) {
                    toastr.error("Error on add!");
                }).always(function (response) {
                    $('#overlay-{$lower}').hide();
                });
            }

            $("#btn-{$lower}").click(function(){
                add{$attact_model}();
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
    
        $css = <<< CSS
        CSS;
    
        \Yii::$app->view->registerCss($css);
        \Yii::$app->view->registerJs($script,View::POS_END);
  
        $button = Html::button(
                    '<i class="fas fa-trash mr-2"></i>' . \Yii::t('app', 'Remove Files'),
                    [
                        'onclick' => 'removeFiles(this)',
                        'class' => 'btn btn-danger',
                        'id' => 'remove-files',
                        "data-toggle" => "tooltip",
                        "data-placement" => "top",
                        "title" => \Yii::t('app', 'Remove Files')
                    ]
                );
    
        $gridView = GridView::widget([
                            'id' => 'grid-files',
                            'dataProvider' =>  $this->dataProvider,
                            'columns' => [
                                [
                                    'class' => 'yii\grid\CheckboxColumn',
                                    // you may configure additional properties here
                                ],
                                $this->show_fields,
                                [
                                    'class'=> ActionColumn::class,
                                    'headerOptions' => ['style' => 'width:10%'],
                                    'template' => '{view}{remove}{delete}',
                                    'path' => 'app',
                                    'controller' => 'file',
                                    'buttons' => [
                                        'remove' => function ($url, $model, $key) {
                                            return AuthController::verAuthorization('file', 'remove-file', $model) ?
                                                Html::a(
                                                    '<i class="fas fa-unlink"></i>',
                                                    yii\helpers\Url::to(['file/remove-file', 'id' => $model->id, 'folder' => $model->folder_id]),
                                                    ['class' => 'btn btn-outline-secondary', "data-toggle" => "tooltip", "data-placement" => "top", "title" => \Yii::t('app', 'Remove from folder')]
                                                ) : '';
                                        },
                                        'delete' => function ($url, $model, $key) {
                                            return
                                                Html::button(
                                                    '<i class="fas fa-trash"></i>',
                                                    ['onclick' => 'removeFiles(this)', 'class' => 'btn btn-outline-secondary', "data-id" => $model->id, "data-toggle" => "tooltip", "data-placement" => "top", "title" => \Yii::t('app', 'Remove')]
                                                );
                                        },
                                    ]
                                ],
                            ],
                        ]);
    
        $head = <<< HTML
            <div class="card" id="list-files">
    
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'List Files'); ?></h3>
                </div>
    
                <div class="card-body">
                    <p>
                    $button
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
        echo Select2::widget([
            'data' => ArrayHelper::map($this->child::find()->asArray()->all(), $this->child_field, $child_field_text),
            'options' => ['placeholder' => 'Select a state ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        echo Html::hiddenInput($root_field)->hiddenInput(['value'=>$model->id])->label(FALSE);
        echo Html::button(Yii::t('*','Add'), ['class' => 'btn btn-weebz']);
        echo $footer;
        echo $head;
        Pjax::begin(['id' => 'list-{$lower}-grid']);
            echo $gridView;
        Pjax::end();
        echo $footer;
   
    }
}