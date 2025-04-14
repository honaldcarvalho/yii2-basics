<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;

class UploadFiles extends Widget
{
    public $extensions = ['jpg','png'];
    /**
     * execute javascript function on end send file
    * */
    public $callback = '';
    /**
     * execute javascript function after upload start
    * */
    public $onstart= '';
    /**
     * show send file list
    * */
    public $show_list = 1;
    /**
     * upload automatic on select files
    * */
    public $auto = 1;
    /**
     * defines the element id where files will be listed
    * */
    public $file_list_el = 'list-upload';

    public $file_element = 'select-files';
    
    public $folder_el = '';
    
    public $folder_id = 'null';

    public $show_upload_label = 0;
    
    public $random = 0;

    public function init(): void
    {
        parent::init();

        $url = \Yii::getAlias('@web');
        $extensions = '"'.implode('","',$this->extensions).'"';
        $csrfToken = \Yii::$app->getRequest()->getCsrfToken();        
        
        \Yii::$app->view->registerJsFile(
            '@web/plugins/axios/axios.min.js'
        );
        
       \Yii::$app->view->registerJsFile(
          '@web/plugins/jquery-cropper/cropper.min.js',
          ['depends' => [\yii\web\JqueryAsset::class]]
        );

        \Yii::$app->view->registerJsFile(
          '@web/plugins/jquery-cropper/jquery-cropper.min.js',
          ['depends' => [\yii\web\JqueryAsset::class]]
        );

        \Yii::$app->view->registerCssFile("@web/plugins/jquery-cropper/cropper.min.css", [
          'depends' => [\yii\bootstrap5\BootstrapAsset::class],
        ], 'cropper');

        $script = <<< JS

        $('#{$this->random}{$this->file_element}').on("change", function(){ createUploadElements(); });
        
        async function listEncodeImageFileAsURL(preview, file) {
            var reader = new FileReader();            
            reader.onloadend = await function () {;
              $(preview).src = reader.result;
            }
            reader.readAsDataURL(file);
        }    
                
        //UPLOAD EVENT
        
        function createUploadElements(){
        
            var fileElement  = $('#{$this->random}{$this->file_element}');
            var files = fileElement.prop('files');
            
            filesSelected = files;
            
            $('#{$this->random}list-upload-container').show();
            $('#{$this->random}{$this->file_list_el}').html('');

            $.each(files, function (indexInArray, file) {
                
                preview =" $url/preview.jpg";
                
                $('#{$this->random}{$this->file_list_el}').append(
                '<div class="col-md-12 d-flex text-muted pt-3" id="{$this->random}'+indexInArray+'-box">'
                +'    <div class="p-5" id="{$this->random}preview-container-'+indexInArray+'"><img src="'+preview+'" id="{$this->random}'+indexInArray+'-preview" class="bd-placeholder-img flex-shrink-0 me-2 rounded" width="100" height="100"></div>'
                +'    <div class="pb-3 mb-0 small lh-sm border-bottom w-100">'
                +'        <div class="d-flex justify-content-between">'
                +'            <strong class="text-gray-dark" id="{$this->random}'+indexInArray+'-file-name">'+file.name+'</strong>'
                +'            <strong class="text-gray-dark" id="{$this->random}'+indexInArray+'-info">Send Files</strong>'
                +'            <div class="button-group">'
                +'              <a id="btn-upload-'+indexInArray+'" class="btn btn-success btn-upload" data-id="{$this->random}'+indexInArray+'" onclick="upload('+indexInArray+')">Enviar</a>'
                +'              <a id="btn-danger-'+indexInArray+'" class="btn btn-success btn-danger" data-id="{$this->random}'+indexInArray+'" onclick="removeBox('+indexInArray+')">Cancelar</a>'
                +'            </div>'
                +'        </div>'
                +'        <span class="d-block" id="{$this->random}'+indexInArray+'-type">'+file.type+'</span>'
                +'        <span class="d-block" id="{$this->random}'+indexInArray+'-type">'+(file.size / 1024 / 1024).toFixed(2)+' Mbs</span>'
                +'        <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="{$this->random}'+indexInArray+'-progressbar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>'
                +'   </div>'
                +'</div>');
                
                if(['image/jpg', 'image/png', 'image/jpeg'].includes(file.type)){
                    listEncodeImageFileAsURL("#{$this->random}"+indexInArray+"-preview",file);
                }
                
            });

            $("#{$this->random}temp-files").replaceWith($("#{$this->random}{$this->file_element}").clone(true).attr('id', "{$this->random}temp-files"));
            $("#{$this->random}{$this->file_element}").replaceWith($("#{$this->random}{$this->file_element}").val('').clone(true))
                
            if($this->auto == 1){

                var fileElementFaux  = $('#{$this->random}temp-files');
                var filesFaux = fileElementFaux.prop('files');

                $.each(filesFaux, function (index, file) {
                    upload(index);
                });
            }
        
        }
        
        function removeBox(id){
            $('#{$this->random}'+id+'-box').remove();
            if($('#{$this->random}{$this->file_list_el}').html().length == 0){
                $('#{$this->random}list-upload-container').hide();
            }
        }
        
        function upload(id){
            var folder_id = $this->folder_id;
            var fileElement  = $('#{$this->random}temp-files');
            var folderElement  = $('{$this->random}{$this->folder_el}');
            var fileElementId = $(this).attr("data-id");  
            var fileBox  = $('#{$this->random}'+ id + '-box');
            var fileProgressBar  = $('#{$this->random}'+ id + '-progressbar');
            var fileInfoProgress  = $('#{$this->random}'+ id + '-info');
            var extensions = [$extensions];

            var file = fileElement.prop('files')[id];
                
            if(folder_id == null){
                folder_id =  folderElement.val();
            }
                
            var formData = new FormData();
            formData.set('_csrf', '$csrfToken');
            formData.set('UploadForm[file]', file);
            formData.set('UploadForm[folder_id]',folder_id);
            formData.set('UploadForm[description]', file.name);
            formData.set('UploadForm[extensions]',extensions);

            fileProgressBar.html('0%');
            fileProgressBar.attr('aria-valuenow', 0).css('width', '0%'); 

            $('#btn-upload-'+id).hide();
            $('#btn-danger-'+id).hide() ;

            axios.post("$url/file/upload", formData, {
                headers: {
                'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: progressEvent => {
                    $this->onstart
                    fileInfoProgress.html('Enviando...');
                    let percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    fileProgressBar.attr('aria-valuenow', percentCompleted).css('width', percentCompleted+'%');       
                    fileProgressBar.attr('aria-valuenow', percentCompleted).css('width', percentCompleted+'%');       
                    fileProgressBar.html(percentCompleted+'%');
                    if(percentCompleted == 100){
                        fileProgressBar.html('Processing...');   
                    }
                }
            }).then(res => {

                if(res.data.error){
                    fileInfoProgress.html('Ocorreu algum erro: ' + res.data.info.file[0]);
                    fileProgressBar.addClass('bg-danger');
                    fileProgressBar.removeClass('bg-success');
                }else{
                    fileInfoProgress.html('Enviado');
                    fileBox.fadeOut(1000,function(){
                        fileBox.remove();
                        if($('#{$this->random}{$this->file_list_el}').html().length == 0){
                            $('#{$this->random}list-upload-container').hide();
                        }
                    });
                    $this->callback     
                }

            })
            .catch(function (error) {
                fileInfoProgress.html('Ocorreu algum erro: '+error.response.data.message);
                fileProgressBar.addClass('bg-danger');
                fileProgressBar.removeClass('bg-success');
                fileProgressBar.attr('aria-valuenow', 0).css('width', '0%');       
                fileProgressBar.html('0%');
            });
        
        };
        
        JS;
        \Yii::$app->view->registerJs($script, View::POS_END);
    }

    public function run()
    {
        $accept = implode(',',$this->extensions);
        $button =
        '<label class="label-file btn btn-outline-warning" data-toggle="tooltip" title="Selecione a Imagem" style="width: 100%;cursor: pointer;">
            <span class="text-center"><i class="fas fa-upload"></i> '.($this->show_upload_label == 1 ? \Yii::t('app','Send Files') : '' ).'</span>
            <input type="file" multiple="true" id="'.$this->random.'select-files" name="'.$this->random.'select-files" onchange="createUploadElements()" style="display:none;" accept="'.$accept.'" >
            <input type="file" multiple="true" id="'.$this->random.'temp-files" name="'.$this->random.'temp-files" style="display:none;" accept="'.$accept.'" >
        </label>';

        $list = 
        '<div id="'.$this->random.'list-upload-container" class="col-md-12 my-3 p-3 bg-body rounded shadow-sm" style="display:none;">
            <h6 class="border-bottom pb-2 mb-0">'.\Yii::t('app','Send Files').'</h6>
            <div class="row" id="'.$this->random.'list-upload">
            </div>
        </div>';

        return $button . ($this->show_list == 1 &&  $this->file_list_el == 'list-upload' ? $list : '');
    }
}