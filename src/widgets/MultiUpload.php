<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;

class MultiUpload extends Widget
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
    public $auto = 0;
    
    public $folder_el = '';
    
    public $folder_id = 'null';

    public $select_label = 'Select Files';
    public $upload_label = 'Send Files';
    


    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $baseUrl = \Yii::getAlias('@web');
        $random = Yii::$app->security->generateRandomString(10);
        $file_list_el = "list-upload-$random";
        $file_element = "select-files-$random";
        $container = "list-upload-container-$random";
        
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
        let total = 0;
        $('#{$file_element}').on("change", function(){ createUploadElements(); });
        
        async function listEncodeImageFileAsURL(preview, file) {
            var reader = new FileReader();            
            reader.onloadend = await function () {
                $(preview).attr('src',reader.result);
            }
            reader.readAsDataURL(file);
        }    
                
        //UPLOAD EVENT
        
        function createUploadElements(){
            var fileElement  = $('#{$file_element}');
            var files = fileElement.prop('files');
            var dummy_image = "{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=NO PREVIEW";
            filesSelected = files;
            
            $('#{$container}').show();
            $('#{$file_list_el}').html('');
            $(`#send-{$random}`).prop('disabled', false);
            
            $.each(files, function (indexInArray, file) {
                total++;
                preview ="{$url}/preview.jpg";
                size = (file.size / 1024 / 1024).toFixed(2);

                if(file.type.search('image') != -1){ icon = '<i class="mr-2 fas fa-file-image text-orange"></i>';
                } else if(file.type.search('text') != -1){ icon = '<i class="mr-2 fas fa-file-alt"></i>';
                } else if(file.type.search('pdf') != -1){ icon = '<i class="mr-2 fas fa-file-pdf  text-warning"></i>';
                } else if((file.type.search('zip') != -1) || (file.type.search('rar') != -1)){
                    icon = '<i class="mr-2 fas fa-file-archive text-warning-emphasis"></i>';
                } else if(file.type.search('document') != -1){ icon = '<i class="mr-2 fas fa-file-word text-primary-emphasis"></i>';
                } else if(file.type.search('sheet') != -1){ icon = '<i class="mr-2 fas fa-file-excel  text-success"></i>';
                } else { icon = '<i class="mr-2 fas fa-file"></i>'; }

                let tr = $(`<tr class="fw-normal" id="upload-\${indexInArray}-{$random}">`);
                let th_preview = $(`<th style="width:15%;max-width:150px;height:auto;text-align:center;">`); 
                let img = $(`<img id="\${indexInArray}-preview-{$random}" src="\${dummy_image}" class="w-100" >`);
                th_preview.append(img); 
                let td_type = $(`<td style="width:15%;" class="align-middle" text-center><i class="mb-0">  \${icon} \${file.type} </i></td>`);
                let td_info = $(`<td style="width:50%;"class="align-middle"></td>`);
                td_info.append(`<i class="ms-2 float-start w-100" id="\${indexInArray}-file-name-{$random}">\${file.name}</i>`); 
                td_info.append($(`<span class="ms-2 float-start w-100 d-block" id="\${indexInArray}-info-{$random}">Wait</span>`));
                td_info.append($(`<div class="float-start d-block" id="\${indexInArray}-progress-{$random}" style="width:100%;display:block;background:#333333;height:20px;position:relative;"><i class="percent-send" style="position:absolute;left:50%;z-index:1">0%</i><div class="load-bar progress-bar-striped bg-success text-center" style="position:absolute;left:0;width:0%;height:20px;border-radius:3px;;z-index:0"></div></div>`));
                let td_actions = $(`<td style="width:20%;" class="align-middle">`);
                let btn_send = $(`<a href="#!" data-mdb-tooltip-init title="Send" class="btn btn-upload" data-id="\${indexInArray}-{$random}" onclick="upload(\${indexInArray})"><i class="fas fa-upload fa-lg text-warning"></i></a>`);
                let btn_del = $(`<a href="#!" data-mdb-tooltip-init title="Remove" class="btn btn-remove" onclick="removeBox(\${indexInArray})"><i class="fas fa-trash-alt fa-lg text-danger"></i></a>`);
                td_actions.append(btn_send);
                td_actions.append(btn_del);
                tr.append(th_preview);
                tr.append(td_type);
                tr.append(td_info);
                tr.append(td_actions);

                $('#{$file_list_el}').append(tr);
                
                if(file.type.search('image') != -1){
                    listEncodeImageFileAsURL(`#\${indexInArray}-preview-{$random}`,file);
                }
                
            });

            $(`#send-{$random} .total-files`).html(`(\${total})`);
            $("#temp-files-{$random}").replaceWith($("#{$file_element}").clone(true).attr('id', "temp-files-{$random}"));
            $("#{$file_element}").replaceWith($("#{$file_element}").val('').clone(true))
                
            if($this->auto == 1){

                var fileElementFaux  = $('#temp-files-{$random}');
                var filesFaux = fileElementFaux.prop('files');

                $.each(filesFaux, function (index, file) {
                    upload(index);
                });
            }
        
        }
        
        function removeBox(id){
            $(`#upload-\${id}-{$random}`).remove();
            if($('#{$file_list_el}').html().length == 0){
                $('#{$container}').hide();
            }
        }
        
        function upload(id) {

            var folder_id = $this->folder_id;
            var fileElement  = $('#temp-files-{$random}');
            var folderElement  = $('{$this->folder_el}-{$random}');
            var fileElementId = $(this).attr("data-id");  
            var fileBox  = $(`#upload-\${id}-{$random}`);
            var fileProgressBar  = $(`#\${id}-progress-{$random} .load-bar`);
            var percentSend  = $(`#\${id}-progress-{$random} .percent-send`);
            
            var fileInfoProgress  = $('#'+ id + '-info-{$random}');
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

            percentSend.html('0%');
            fileProgressBar.attr('aria-valuenow', 0).css('width', '0%'); 

            $('#btn-upload-'+id).hide();
            $('#btn-danger-'+id).hide() ;

            axios.post("$url/file/upload", formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: progressEvent => {
                    $this->onstart
                    percentSend.html('Enviando...');
                    let percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    fileProgressBar.css('width', percentCompleted+'%');             
                    percentSend.html(percentCompleted+'%');
                    if(percentCompleted == 100){
                        percentSend.html('Processing...');   
                    }
                }
            }).then(res => {
                if(res.data.error){
                    fileInfoProgress.html('Ocorreu algum erro: ' + res.data.info.file[0]);
                    fileProgressBar.addClass('bg-danger');
                    fileProgressBar.removeClass('bg-success');
                }else{
                    percentSend.html('Enviado');
                    total--;
                    $(`#send-{$random} .total-files`).html(`(\${total})`);  
                    if(total <= 0){
                        $(`#send-{$random}`).prop('disabled', true);
                    }
                    fileBox.fadeOut(1000,function(){
                        fileBox.remove();
                        if($('#{$file_list_el}').html().length == 0){
                            $('#{$container}').hide();
                        }
                    });
                    $this->callback     
                }

            })
            .catch(function (error) {
                fileInfoProgress.html('Ocorreu algum erro: '+error.response.data.message);
                fileProgressBar.addClass('bg-danger');
                fileProgressBar.removeClass('bg-success');
                fileProgressBar.css('width', '0%');
            });
        
        };
        
        function uploadElements(){
            var fileElement  = $('#temp-files-{$random}');
            var files = fileElement.prop('files');

            $.each(files, function (indexInArray, file) {
                upload(indexInArray);
            });

        }
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        $accept = implode(',',$this->extensions);
        $select_button = \Yii::t('app',$this->select_label);
        $send_button = \Yii::t('app',$this->upload_label);

        $buttons = <<< HTML
        <div class="row">
            <label class="label-file btn btn-warning col-lg-6 col-md-12 m-0" data-toggle="tooltip" title="Select Files"">
                <span class="text-center"><i class="fas fa-file"></i> {$select_button} </span>
                <input type="file" multiple="true" id="select-files-{$random}" name="select-files-{$random}" onchange="createUploadElements()" style="display:none;" accept="{$accept}" >
                <input type="file" multiple="true" id="temp-files-{$random}" name="temp-files-{$random}" style="display:none;" accept="'.$accept.'" >
            </label>
            <button class="btn btn-success col-lg-6 col-md-12" id="send-{$random}" disabled="true" onclick="uploadElements()">
                <span class="text-center"><i class="fas fa-upload"></i> {$send_button} <i class="total-files"></i></span>
            </button>
        </div>
        HTML;
        
        $list = <<< HTML
        
            <table id="{$container}" style="display:none;" class="table text-white table-striped mb-0">
              <thead>
                <tr>
                  <th scope="col">Preview</th>
                  <th scope="col">Type</th>
                  <th scope="col">Info</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody id="{$file_list_el}">

              </tbody>
            </table>

        HTML;

        return $buttons . $list;
    }
}