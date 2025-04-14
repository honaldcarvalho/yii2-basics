<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\models\File;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;
use weebz\yii2basics\models\Folder;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use yii\helpers\ArrayHelper;

class SelectFile extends Widget
{
    /**
     * defines the element that will receive the selected file
    * */
    public $field_id = '#file_id';

    public $model_field = 'file_id';
    
    public $folder_id = '';
    /**
     * defines the selected data
    * */
    public $mode = 'id';
    /**
     * set file model
    * */
    public $model = null;
    /**
     * set folder image
    * */
    public $folder = '';
    /**
     * set data mimetype
    * */
    public $extensions = ['jpg','png'];
    /**
     * defines if the image preview will be showed
    * */
    public $preview_image = 0;
    
    
    public $auto_upload = 0;

    public $file_list_el = 'list-upload-select';

    public $onSelect = '';

    public $random = 0;
    
    /**
     * defines if the image preview element
    * */
    public $preview_image_el = 'preview';
    
    public function init(): void
    {
        parent::init();
    }

    public function run()
    {
        

        $preview = "/preview.jpg";
        $select = \Yii::t('app','Select');
        $load = \Yii::t('app','Load More');
        $all = \Yii::t('app','All');
        $extensions = '"'.implode('","',$this->extensions).'"';
        $this->random = rand(10000,99999);

        $thumb =  '';
        $show_clear  = 'block';
        $show_select  = 'none';
        $show_thumb  = 'none';
        $preview_description = '';
        
        if(isset($this->model)){

            if($this->mode == 'id'){
                $file = File::findOne($this->model->{$this->model_field});
                $show_thumb  =  (!empty($thumb) && $this->preview_image) ? 'block' : 'none';
                if($file === null){
                    $show_select  = 'block';
                    $show_clear = 'none';
                } else {
                    $preview_description = $file->description ?? $file->name;
                    $thumb = $file->urlThumb ?? '';
                }
            }
        }

        PluginAsset::register(\Yii::$app->view)->add(['datatables','jquery-cropper']);
        
        $dropdown_folder = Html::dropDownList("{$this->random}-select_folder_id",null,ArrayHelper::map(Folder::find()->asArray()->all(), 
        'id', 'name'), ['prompt' =>\Yii::t('app','-- Folder --'),'class'=>'form-control','id'=>"{$this->random}-select_folder_id"]);

        $table = <<< HTML
        <div class="row mb-5 search-form">
            <div class="col-md-8">
                <input class="form-control" type="search" placeholder="Search" aria-label="Search" id="{$this->random}-str_search">
            </div>
            <div class="col-md-2">
                {$dropdown_folder}
            </div>
            <div class="col-md-1">
                <a class="btn btn-outline-success my-2 my-sm-0" id="search"  href="javascript:;"><i class="fas fa-search"></i></a>
            </div>
        </div>

        <table class="table" id="{$this->random}-data_table">
            <thead>
                <tr>
                    <th scope="col">Description</th>
                    <th scope="col">Preview</th>
                    <th scope="col"></th>
                </tr>
            </thead>
        <tbody class="overflow-y-scroll h-50" ></tbody></table>
        HTML;

        $script = <<< JS

            var table = null;
            var count = 0;
            var total_resutados = 0;
            var files = [];
            var modal_list_files = $('#{$this->random}-modal-list-files');

            $('#{$this->random}-btn-load').click(function() {
                if(count <= total_resutados){
                    $(this).html('Loading...');
                    $(this).prop('disabled', true);
                    count += 10;
                    getData();
                }else{
                    $(this).hide();
                }
            });

            $('.search-form').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) { 
                    count = 0;
                    getData();
                    e.preventDefault();
                    return false;
                }
            });

            function selectFile(id){
                let selected = null;
                $.each(files, function(i, file) {
                    if(file.id == id){
                        selected = file;
                    }
                });
  
                if('$this->mode' == 'id'){
                    $('$this->field_id').val(selected.id);
                }else{
                    $('$this->field_id').val(selected.url);
                }
                $this->onSelect
                $('#{$this->random}-file_preview_description').html(selected.description);

                if('$this->preview_image' == 1){
                    $('#$this->preview_image_el').attr('src',selected.urlThumb);
                }
                if('$show_thumb' == 'block'){
                    $('#$this->preview_image_el').show();
                }
                $('#{$this->random}-btn-select-file').hide();
                $('#{$this->random}-btn-clear-file').show();
                modal_list_files.modal('hide');
            }
                
            function clearFile(){
                $('$this->field_id').val('');
                $('#{$this->random}-btn-select-file').show();
                $('#{$this->random}-file_preview_description').html('');
                $('#$this->preview_image_el').hide();
                $('#{$this->random}-btn-clear-file').hide();
            }

            function getData(action){
                if(action == 'search')
                    count = 0;
                var descricao;
                var data = {       
                    "folder_id": $("#{$this->random}-select_folder_id").val(),
                    "str_search": $("#{$this->random}-str_search").val(),
                    "extensions": [$extensions],
                    "count":count
                };
                $('#{$this->random}-overlay-search-file').show();
                $.ajax({
                    url: "/file/list",
                    type: 'POST',
                    //contentType: 'application/json; charset=utf-8',
                    data:data
                }).done(function(dados){
                    files = dados;
                    $('#{$this->random}-overlay-search-file').hide();
                    reloadDataTable(dados,action);
                    total_resutados = dados.length;
                    if(total_resutados >= 10){
                        $('#{$this->random}-btn-load').show();
                    }
                });
            }

            function reloadDataTable(dados){
                if(table !== null) {
                    table.destroy();
                    table = null;
                }

                if(count == 0)
                    $('#{$this->random}-data_table tbody').html('');
                
                $.each(dados, function(i, file) {
                    var tr = $('<tr>').append(
                        $('<td>').text(file.description),
                        $('<td>').html(file.urlThumb.length > 0 ? '<img width="100" src="'+file.urlThumb+'" />' : '<img width="250" src="https://dummyimage.com/250x100/cfcfcf/000000&text=NO+PREVIEW" />' ),
                        $('<td>').html('<a class="btn btn-warning" href="javascript:selectFile('+file.id+');"> $select </a>'),
                    ).appendTo('#{$this->random}-data_table tbody');                    
                });
                
                table = $('#{$this->random}-data_table').DataTable({
                    'fixedHeader': true,
                    'lengthMenu': [ [10, 50, 100, -1], [10, 25, 100, '$all' ] ],
                    'ordering': false,
                    'buttons': false,
                    'search': false
                });

                $('#{$this->random}-btn-load').html("$load");
                $('#{$this->random}-btn-load').prop('disabled', false);

            }

            $(function(){
                getData('list');
                $("#{$this->random}-select_folder_id").val($this->folder_id);
                $('#search').click(function(){
                    getData('search');
                });
            });
        JS;
        \Yii::$app->view->registerJs($script, View::POS_END);
        
        $preview = <<< HTML
        <div class='row'>
            <img style='display:{$show_thumb};width:250px'; src='{$thumb}' id='{$this->preview_image_el}'>
            <p>
                <label id='{$this->random}-file_preview_description'>{$preview_description}</label>
            </p>
        </div>
        HTML;

        $buttons = <<< HTML
        <div class='row'>
            <a style='display:{$show_select}'; id='{$this->random}-btn-select-file'class='btn btn-primary' onclick="javascript:modal_list_files.modal('show');">Select File</a>
            <a style='display:{$show_clear}';  id='{$this->random}-btn-clear-file' class='btn btn-default' href='javascript:clearFile();'>Clear File</a>
        </div>
        HTML;
        
        $modal = <<< HTML
        <div class='modal fade' id='{$this->random}-modal-list-files'>
            <div class='modal-dialog modal-xl'>
                <div class='modal-content'>
                    <div id='{$this->random}-overlay-search-file' class='overlay' style='height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                        <div class='d-flex align-items-center'>
                        <strong>Loading...</strong>
                        <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                        </div>
                    </div>
                    <div class='modal-header'>
                        <h5 class='modal-title'>File List</h5>
                        <button type='button' class='btn-close' data-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body position-relative'>
                        <div class='row' id='{$this->random}{$this->file_list_el}'>
                        </div>
                        {$table}
                        <div class='d-flex justify-content-center'>
                            <button id='{$this->random}-btn-load' class='btn btn-success' type='button' style='display:none;'>
                            Load more
                            </button>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
        HTML;

        return $preview.$buttons.$modal;
    }
}