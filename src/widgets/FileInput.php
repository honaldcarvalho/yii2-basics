<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use Yii;
use yii\web\View;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\Widget;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\File $model */
/** @var yii\widgets\ActiveForm $form */

class FileInput extends Widget
{
    public $value = null;
    public $field_name = 'file';
    public $label = '';
    public $type = 'image';
    public $action = 'file/upload';
    public $save_file_model = 0;
    public $aspectRatio = 'auto';
    public $file_name = '';
    public $folder_id = 1;
    public $callback = '';
    public $auto = 0;
    public $as_blob = 0;
    public $extensions = ['jpg', 'png', 'jpeg'];
    public $file_element = "file-upload";
    public $crop_preview = 'crop_preview_upload';
    public $onUploadProgress = "";
    public $onError = "";
    public $onSuccess = "";

    public function init(): void
    {
        parent::init();
    }

    public function run()
    {
        $baseUrl = \Yii::getAlias('@web');
        $random = Yii::$app->security->generateRandomString(10);
        $this->crop_preview = 'crop-preview-upload-' . $random;
        $extensions = implode(',.', $this->extensions);

        $url = \Yii::getAlias('@web');
        $csrfToken = \Yii::$app->getRequest()->getCsrfToken();
        $extensions = '"' . implode('","', $this->extensions) . '"';

        $css = <<< CSS
        .custom-file-upload {
            position: relative;
            display: inline-block;
        }

        .custom-file-upload{
            width:250px;
            height:250px;
            overflow:hidden;
            border-radius: 5px;
            padding:0;
            cursor: pointer;
            overflow: hidden;
            text-align: left;
            background-color: #ffffff;
            font-family: 'Roboto', sans-serif;
        }

        .custom-file-upload img {
            width:250px;
        }

        .custom-file-upload button:hover {
            background-color: #388e3c;
        }
        .custom-file-upload button::before {
            content: "\f0e4";
            font-family: FontAwesome;
            margin-right: 5px;
        }
        .custom-file-upload input[type="file"] {
            position:absolute;
            color: black;
            width: 100%;
            height: 100%;
            z-index:3000;
            opacity: 0%;
        }
      CSS;

        PluginAsset::register(Yii::$app->view)->add(['axios','jquery-cropper']);
        \Yii::$app->view->registerCss($css);


        $script = <<< JS
      
        let editing = false;
        let cropperImage = null;
        let image = null;
        let imageCroped = null;
        let preview = $("#{$this->crop_preview}");
        let preview_crop = $("#{$this->crop_preview}_crop");
        let file = null;
        var file_element;

        function startCrop() {
            $('.crop-tool-{$random}').removeClass('d-none');
            $('#btn-start-crop-{$random}').addClass('d-none');
            $('#btn-upload-{$random}').addClass('d-none');

            image = preview_crop;
            
            image.cropper({
                aspectRatio: '{$this->aspectRatio}',
                crop: function (event) { }
            });
            cropperImage = image.data('cropper');
        }
        
        function cancelCrop() {
            $('.crop-tool-{$random}').addClass('d-none');
            $('#btn-start-crop-{$random}').removeClass('d-none');
            $('#btn-upload-{$random}').removeClass('d-none');

            image.cropper("destroy");
        }
        
        function setCrop() {

            $('.crop-tool-{$random}').addClass('d-none');
            $('#btn-start-crop-{$random}').removeClass('d-none');
            $('#btn-upload-{$random}').removeClass('d-none');
            
            var canvas = cropperImage.getCroppedCanvas();
            var data = canvas.toDataURL();
            preview.attr('src',data);
            preview_crop.attr('src',data);
            
            image.cropper("destroy");
        
            canvas.toBlob(function (blob) {
                var file = new File([blob], "{$random}-imagem.jpg", {type: "image/jpeg", lastModified: new Date().getTime()});
                var container = new DataTransfer();
                container.items.add(file);
                var fileField = document.getElementById('file-upload');
                fileField.files = container.files;
            });

        }
        
        $(document).ready(function() {
            var modalCrop = new bootstrap.Modal(document.getElementById('modal-crop'), {
                keyboard: false
            });

            file_element = document.getElementById('{$this->file_element}');

            if($this->auto == 1){
                $('#$this->file_element').on("change", function(){ 
                    file = file_element.files[0]; 
                    upload(); 
                });
            }else{

                $('#{$this->file_element}').on("change", function(){
                    file = file_element.files[0]; 
                    const file_types = ["image/jpg", "image/jpeg", "image/png", "image/gif"];
                    let is_image = file_types.includes(file.type);
                    $('#btn-remove-{$random}').removeClass('d-none');
                    if(is_image) {
                        encodeImageFileAsURL(); 
                        $('#btn-start-crop-{$random}').removeClass('d-none');
                    }else{
                        $("#{$this->crop_preview}").attr('src','$baseUrl/dummy/code.php?x=150x150/fff/000.jpg&text=NO PREVIEW');
                        $('#btn-start-crop-{$random}').addClass('d-none');
                    }
                    if({$this->as_blob} == 0) {
                        $('#btn-upload-{$random}').removeClass('d-none');
                    }
                    $('#btn-remove-{$random}').removeClass('d-none');
                    $('.file-info-{$random}').removeClass('d-none');
                    $('#file-type-{$random}').html('Type: ' + file.type);
                    $('#file-description-{$random}').val(file.name);
                    $('#btn-upload-{$random}').show();  
                });
            }

            $('#btn-start-crop-{$random}').click(function () {
                if (editing === false) {
                    modalCrop.show();
                    startCrop();
                    editing = true;
                }
            });
        
            $('#btn-cancel-crop-{$random}').click(function () {
                if (editing) {
                    modalCrop.hide();
                    cancelCrop();
                    editing = false;
                }
            });
        
            $('#btn-set-crop-{$random}').click(function () {
                if (editing) {
                    $('#crop-load').removeClass('d-none');
                    /** para granratir que o overlay apareça */
                    setTimeout(function() { 
                        modalCrop.hide();
                        setCrop();
                        editing = false;
                        $('#crop-load').addClass('d-none');
                    }, 100); 
                }
            });
        
        }); 
        
        function encodeImageFileAsURL() {

                var reader = new FileReader();

                reader.onloadend = function () {
                    preview.attr('src',reader.result);
                    preview_crop.attr('src',reader.result);
                    preview.css('display','block');
                    if({$this->as_blob} == 1){
                        document.getElementById('{$this->field_name}').value = reader.result;
                    }
                }
                reader.readAsDataURL(file);
        }

        function removeImage() {
            document.getElementById('{$this->field_name}').value = '';
            preview.attr('src',"{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=NO IMAGE");
            $('.file-info-{$random}').addClass('d-none');
            $('#{$this->file_element}').val('');
        }

        $('#btn-remove-{$random}').on('click', function(){ 
            removeImage();     
        });

        $('#btn-upload-{$random}').on('click', function(){
            upload();     
        });
        
        function upload(){

            var extensions = [$extensions];

            var formData = new FormData();
            formData.set('_csrf', '{$csrfToken}');
            formData.set('UploadForm[file]', file);
            if("{$this->file_name}".trim() != ''){
                formData.set('UploadForm[file_name]', "{$this->file_name}");
            }
            formData.set('UploadForm[folder_id]', {$this->folder_id});
            formData.set('UploadForm[description]', $("#file-description-{$random}").val());
            formData.set('UploadForm[save_file_model]', {$this->save_file_model});
            formData.set('UploadForm[extensions]', extensions);
            
            axios.post("{$url}/{$this->action}", formData, {
                'headers': {
                'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: progressEvent => {
                    {$this->onUploadProgress  }
                    $('#overlay-{$random}').show();
                }
            }).then(res => {
                if(res.data.error){
                    {$this->onError}
                    $('#send-info').html('Ocorreu algum erro: ' + res.data.info.file[0]);
                }else{
                    {$this->onSuccess}
                    $('#send-info-{$random}').html('Enviado');
                    $('#btn-upload-{$random}').hide();
                    $('#{$this->field_name}').val(res.data.model.id);
                }
            }).catch(function (error) {
                {$this->onError}
                $('#send-info-{$random}').html('Ocorreu algum erro.');
            }).finally(function () {
                $('#overlay-{$random}').hide();
            });

        }
      JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        if($this->value !== null && !empty($this->value)){
            $image = $this->value;
            $show_info = '';
        }else{
            $image = "{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=SELECT FILE";
            $show_info = 'd-none';
        }

        $cut_label = Yii::t('app', 'Cut');
        $cancel_label = Yii::t('app', 'Cancel');
        $upload_label = Yii::t('app', 'Upload');
        $edit_label = Yii::t('app', 'Edit');
        $remove_label = Yii::t('app', 'Remove');

        $modal = <<< HTML
            <div class="modal position-relative" tabindex="-1" id="modal-crop">
                <div id="crop-load" class="justify-content-center d-none" style="width:100%;height:100%;position:absolute;background:#000000ab;top:0;left:0;z-index:3000;">
                <div class="d-flex justify-content-center">
                    <h4>Cropping... Wait...</h4>
                    <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cropping...</span>
                    </div>
                </div>
                </div>
                
                <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-body">
                        <img id="{$this->crop_preview}_crop" class="img-fluid rounded mb-2 mb-lg-0 " src="{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=NO IMAGE PREVIEW" style="width:100%;max-width:350px;">
                    </div>
                    <div class="modal-footer">
                    <a class="btn btn-warning crop-tool-{$random} d-none" href="javascript:;" id="btn-set-crop-{$random}"><i class="fas fa-cut"></i> {$cut_label} </a>
                    <a class="btn btn-danger crop-tool-{$random} d-none" href="javascript:;" id="btn-cancel-crop-{$random}"><i class="fas fa-window-close"></i> {$cancel_label} </span></a>
                    </div>
                </div>
                </div>
            </div>
        HTML;

        $form_upload = <<< HTML
            {$modal}
            <div class="col-12 col-sm-auto mb-3">
                <div class="card border-success mb-3" id="preview-group-{$random}" >
                    <div id="overlay-{$random}" class="overlay" style="height: 100%;position: absolute;width: 100%;z-index: 3000;display:none;top:0;left:0;">
                        <div class="fa-3x">
                            <i class="fas fa-sync fa-spin"></i>
                        </div>
                    </div>
                    <div class="card-header">{$this->label}</div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="custom-file-upload" id="custom-file-upload">
                                <textarea class="d-none" type="text" id="{$this->field_name}" name="{$this->field_name}">{$this->value}</textarea>
                                <input type="file" id="{$this->file_element}" name="{$this->file_element}" accept="{$extensions}">
                                <img id="{$this->crop_preview}" class="img-fluid rounded mb-2 mb-lg-0 " src="{$image}" />
                            </div>
                        </div>

                        <div class="text-center text-sm-left mb-2 mb-sm-0 file-info-{$random} d-none">
                            <div class="pt-sm-2 pb-1 mb-0 text-nowrap"><input class="form-control" id="file-description-{$random}" type="text" placeholder="File Description"></div>
                            <div class="text-muted"><small id="file-type-{$random}"></small></div>
                        </div>
                        
                    </div>

                    <div class="card-footer bg-transparent border-success btn-group file-info-{$random} {$show_info}">
                        <a class="btn btn-warning d-none" href="javascript:;" id="btn-upload-{$random}"><i class="fas fa-upload"></i> {$upload_label}</a>
                        <a class="btn btn-success d-none" href="javascript:;" id="btn-start-crop-{$random}"><i class="fas fa-pencil"></i> {$edit_label}</a>
                        <a class="btn btn-danger" href="javascript:;" id="btn-remove-{$random}"><i class="fas fa-trash"></i> {$remove_label}</a>
                    </div>

                    <div class="card-footer bg-transparent" id="send-info-{$random}">
                    </div>
                </div>
            </div>


        HTML;
        echo $form_upload;
    }
}