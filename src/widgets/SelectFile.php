<?php

namespace weebz\yii2basics\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap5\BootstrapAsset;
use yii\web\JqueryAsset;
use weebz\yii2basics\models\Folder;
use yii\helpers\ArrayHelper;

class SelectFile extends Widget
{
    /**
     * defines the element that will receive the selected file
    * */
    public $field = '#file_id';
    
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
    
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        
        $url = \Yii::getAlias('@web');

        $preview = "/preview.jpg";
        $select = \Yii::t('app','Select');
        $load = \Yii::t('app','Load More');
        $all = \Yii::t('app','All');
        $extensions = '"'.implode('","',$this->extensions).'"';
        $this->random = rand(10000,99999);

        \Yii::$app->view->registerJsFile(
            '/plugins/datatables/jquery.dataTables.min.js',
            ['depends' => [JqueryAsset::class]]
        );

        \Yii::$app->view->registerCssFile("/plugins/datatables/datatables.min.css", [
            'depends' => [BootstrapAsset::class],
        ], 'datatables');
        $table ='
        <div class="row mb-5 search-form">
            <div class="col-md-1">
                '. UploadFiles::widget(['folder_el'=>'#select_folder_id',
                'extensions'=>$this->extensions,
                'show_list'=>1,
                'auto'=>$this->auto_upload,
                'file_list_el'=>$this->file_list_el,
                'onstart'=>'$("#'. $this->random.$this->file_list_el.'").show();$(".search-form").hide();',
                'callback'=>'$("#'. $this->random.$this->file_list_el.'").hide();$(".search-form").show();getData();',
                'random'=>   $this->random 
                ]) .'
            </div>
            <div class="col-md-8">
                <input class="form-control" type="search" placeholder="'.\Yii::t('app','Search').'" aria-label="'.\Yii::t('app','Search').'" id="str_search">
            </div>
            <div class="col-md-2">
                '.Html::dropDownList('select_folder_id',null,ArrayHelper::map(Folder::find()->asArray()->all(), 
            'id', 'name'), ['prompt' =>\Yii::t('app','-- Folder --'),'class'=>'form-control','id'=>'select_folder_id']).'
            </div>
            <div class="col-md-1">
                <a class="btn btn-outline-success my-2 my-sm-0" id="search"  href="javascript:;"><i class="fas fa-search"></i></a>
            </div>
        </div>

        <table class="table" id="data_table">
            <thead>
                <tr>
                    <th scope="col">'.\Yii::t('app','Description').'</th>
                    <th scope="col">Preview</th>
                    <th scope="col"></th>
                </tr>
            </thead>
        <tbody class="overflow-y-scroll h-50" ></tbody></table>';

        $script = <<< JS

            var table = null;
            var count = 0;
            var total_resutados = 0;
            var files = [];

            $('#btn-load').click(function() {
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
                    $('$this->field').val(selected.id);
                }else{
                    $('$this->field').val(selected.url);
                }
                $this->onSelect
                $('#file_preview_description').html(selected.description);

                if('$this->preview_image' == 1){
                    $('#$this->preview_image_el').attr('src',selected.urlThumb);
                }
                $('#$this->preview_image_el').show();
                $('#btn-select-file').hide();
                $('#btn-clear-file').show();
                $('#modal-files').modal('toggle');
            }
                
            function clearFile(){

                $('$this->field').val('');
                $('#btn-select-file').show();
                $('#file_preview_description').html('');
                $('#$this->preview_image_el').hide();
                $('#btn-clear-file').hide();
                
            }

            function getData(){
                var descricao;
                var data = {       
                    "folder_id": $("#select_folder_id").val(),
                    "str_search": $("#str_search").val(),
                    "extensions": [$extensions],
                    "count":count
                };

                $('#overlay-search-file').show();

                $.ajax({
                    url: "/file/list",
                    type: 'POST',
                    //contentType: 'application/json; charset=utf-8',
                    data:data
                }).done(function(dados){
                    files = dados;
                    $('#overlay-search-file').hide();
                    reloadDataTable(dados);
                    total_resutados = dados.length;
                    if(total_resutados >= 10){
                        $('#btn-load').show();
                    }
                });
            }

            function reloadDataTable(dados){

                if(table !== null) {
                    table.destroy();
                    table = null;
                }

                if(count == 0)
                    $('#data_table tbody').html('');
                
                $.each(dados, function(i, file) {
                    var tr = $('<tr>').append(
                        $('<td>').text(file.description),
                        $('<td>').html(file.urlThumb.length > 0 ? '<img width="100" src="'+file.urlThumb+'" />' : '<img width="250" src="https://dummyimage.com/250x100/cfcfcf/000000&text=NO+PREVIEW" />' ),
                        $('<td>').html('<a class="btn btn-warning" href="javascript:selectFile('+file.id+');"> $select </a>'),
                    ).appendTo('#data_table tbody');                    
                });
                
                table = $('#data_table').DataTable({
                    'fixedHeader': true,
                    'lengthMenu': [ [10, 50, 100, -1], [10, 25, 100, '$all' ] ],
                    'ordering': false,
                    'buttons': false,
                    'search': false
                });

                $('#btn-load').html("$load");
                $('#btn-load').prop('disabled', false);

            }

            $(function(){
                getData();
                $("#select_folder_id").val($this->folder_id);
                $('#search').click(function(){
                    getData();
                });
            });
        JS;
        \Yii::$app->view->registerJs($script, View::POS_END);
        
        $thumb =  '';
        $show_thumb  = 'none';
        $show_select  = 'none';
        $preview_description = '';
        
        if(isset($this->model)){

            $preview_description = $this->model->file->description ?? '';
            $thumb = $this->model->file->urlThumb ?? '';
            
            if(!empty($thumb)){
                $thumb = "$thumb";
            }
            $show_thumb  = $this->model->file ? 'block' : 'none';
            $show_select  = !$this->model->file ? 'block' : 'none';
        }
        
        $preview = "<div class='row'><img style='display:{$show_thumb};width:250px'; src='{$thumb}' id='{$this->preview_image_el}'>"
        . "<p><label id='file_preview_description'>{$preview_description}</label></p>"
        ."</div>";

        $buttons = "<div class='row'>"
        ."<a style='display:{$show_select}'; id='btn-select-file'class='btn btn-primary'  data-toggle='modal' data-target='#modal-files'> ". \Yii::t('app','Select File'). "</a>"
        ."<a style='display:{$show_thumb}';  id='btn-clear-file' class='btn btn-default' href='javascript:clearFile();'>". \Yii::t('app','Clear File'). "</a>"
        ."</div>";
        
        $modal = "<div class='modal fade' id='modal-files'>
            <div class='modal-dialog modal-xl'>
                <div class='modal-content'>
                    <div id='overlay-search-file' class='overlay' style='height: 100%;position: absolute;width: 100%;z-index: 3000;top: 0;left: 0;background: #0000004f;'>
                        <div class='d-flex align-items-center'>
                        <strong> ". \Yii::t('app','Loading...'). "</strong>
                        <div class='spinner-border ms-auto' role='status' aria-hidden='true'></div>
                        </div>
                    </div>
                    <div class='modal-header'>
                        <h5 class='modal-title'>". \Yii::t('app','File List'). "</h5>
                        <button type='button' class='btn-close' data-dismiss='modal' aria-label=' ". \Yii::t('app','Close'). "'></button>
                    </div>
                    <div class='modal-body position-relative'>
                        <div class='row' id='{$this->random}{$this->file_list_el}'>
                        </div>
                        {$table}
                        <div class='d-flex justify-content-center'>
                            <button id='btn-load' class='btn btn-success' type='button' style='display:none;'>
                            ". \Yii::t('app','Load more'). "
                            </button>
                        </div>  
                    </div>
                </div>
            </div>
        </div>";

        return $preview.$buttons.$modal;
    }
}