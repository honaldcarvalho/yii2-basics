<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\web\View;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap4\Widget;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\File $model */
/** @var yii\widgets\ActiveForm $form */

class FileInput extends Widget
{
    /** FOR ALL BOOLEAN ATTRIBUTES
     *  1: true
     *    0: false
     * 
        <?=
        weebz\yii2basics\widgets\FileInput::widget(
            [
                'save_file_model' => 1, //indica se vai sarvar o modelo arquivo no banco
                'model' => 'User', // nome do modelo que vai receber o dado do arquivo
                'model_field' => 'file_id', // campo que vai receber o dado do arquivo
                'model_id' => $model->id ?? null, // valor para o campo que vai receber o dado do arquivo
                'value' => $model->file_id ?? null, // valor inicial para o campo que recebe o valor do arquivo
                'preview' => $model->file->url  ?? null,
                'field_id' => 'user-file_id', // id do input
                'field_name' => 'User[file_id]', // nome do input
                'file_name' => '', // nome arquivo caso não queira randomico
                'label' => 'Logo',
                'folder_id' => 3,
                //'action' => 'file/send',
                'as_type' => 0, Tipo do valo que o campo input vai receber: 0 => id, 1 => blob, 2 =>url
                'aspectRatio' => '1/1',
                'extensions' => ['jpeg', 'jpg', 'png'],
                'class'=> '' //css extra class

            ]
        );
        ?>
     */

    public $model = null;
    public $model_id = null;
    public $model_field = null;

    public $preview = null;
    public $value = null;
    public $field_id = 'file';
    public $field_name = 'file';
    public $label = '';
    public $type = 'image';
    public $action = 'file/send';
    public $save_file_model = 0;
    public $aspectRatio = 'auto';
    public $file_name = '';
    public $folder_id = 1;
    public $callback = '';
    public $auto = 0;
    public $style_class = 'rounded';
    /** TYPE
     *  0: id
     *  1: blob
     *  2: url
     */
    public $as_type = 0;
    public $extensions = ['jpg', 'png', 'jpeg'];
    public $file_element = "file-upload";
    public $crop_preview = 'crop_preview_upload';
    public $onUploadProgress = "";
    public $onError = "";
    public $onSuccess = "";
    public $success_message = "Enviado com Sucesso";

    public function init()
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
            width:200px;
            height:200px;
            overflow:hidden;
            border-radius: 5px;
            padding:0;
            cursor: pointer;
            overflow: hidden;
            text-align: left;
            background-color: none;
            font-family: 'Roboto', sans-serif;
        }

        .custom-file-upload img {
            width:150px;
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

        \Yii::$app->view->registerJsFile(
            "$url/plugins/axios/axios.min.js"
        );

        \Yii::$app->view->registerJsFile(
            "$url/plugins/jquery-cropper/cropper.min.js",
            ['depends' => [\yii\web\JqueryAsset::class]]
        );

        \Yii::$app->view->registerJsFile(
            "$url/plugins/jquery-cropper/jquery-cropper.min.js",
            ['depends' => [\yii\web\JqueryAsset::class]]
        );

        \Yii::$app->view->registerCssFile("$url/plugins/jquery-cropper/cropper.min.css", [
            'depends' => [BootstrapAsset::class],
        ], 'cropper');

        \Yii::$app->view->registerJsFile("https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js", [
            ['depends' => [\yii\web\JqueryAsset::class]]
        ], 'popper');

        \Yii::$app->view->registerJsFile("https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js", [
            ['depends' => [\yii\web\JqueryAsset::class]]
        ], 'bootstrap');

        \Yii::$app->view->registerCss($css);


        $script = <<< JS
        
        let editing = false;
        let cropperImage = null;
        let image = null;
        let imageCroped = null;
        let preview = $("#{$this->crop_preview}");
        let preview_crop = $("#{$this->crop_preview}_crop");
        let file = null;
        let file_element;
        let old_preview;

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
                var fileField = document.getElementById('{$this->file_element}');
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

                    if(is_image) {
                        encodeImageFileAsURL(); 
                        $('#btn-start-crop-{$random}').removeClass('d-none');
                    }else{
                        $("#{$this->crop_preview}").attr('src','$baseUrl/dummy/code.php?x=150x150/fff/000.jpg&text=NO PREVIEW');
                        $('#btn-start-crop-{$random}').addClass('d-none');
                    }
                    if({$this->as_type} != 1) {
                        $('#btn-upload-{$random}').removeClass('d-none');
                    }
                    $('.file-info-{$random}').removeClass('d-none');
                    $('#file-type-{$random}').html('Type: ' + file.type);
                    $('#file-description-{$random}').val(file.name);
                    $('#btn-upload-{$random}').show();  
                });
            }

            $('#btn-cancel-{$random}').click(function () {
                if("{$this->preview}" !== ''){
                    preview.attr('src',"{$this->preview}");
                }else{
                    preview.attr('src',"{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=SELECT FILE");
                }
                $('.file-info-{$random}').addClass('d-none');
            });

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
                if({$this->as_type} == 1){
                    document.getElementById('{$this->field_name}').value = reader.result;
                }
            }
            reader.readAsDataURL(file);
        }

        $('#btn-upload-{$random}').on('click', function(){
            upload();     
        });
        
        function upload(){
            var extensions = [$extensions];
            var percentSend  = $(`#file-progress-{$random} .percent-send`);
            var fileProgressBar  = $(`#file-progress-{$random} .load-bar`);
            file = file_element.files[0]; 

            percentSend.html('0%');
            fileProgressBar.attr('aria-valuenow', 0).css('width', '0%'); 
            
            $('#send-info-{$random}').show();

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

            formData.set('UploadForm[model]', '{$this->model}');
            formData.set('UploadForm[model_id]', "{$this->model_id}");
            formData.set('UploadForm[model_field]', "{$this->model_field}");
            formData.set('UploadForm[model_field_id]',  $("#{$this->field_id}").val());
            
            axios.post("{$url}/{$this->action}", formData, {
                'headers': {
                'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: progressEvent => {
                    {$this->onUploadProgress}

                    let percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    fileProgressBar.css('width', percentCompleted+'%');             
                    percentSend.html(percentCompleted+'%');
                    if(percentCompleted == 100){
                        percentSend.html('Processing...');   
                    }
                    
                    $('#overlay-{$random}').show();
                }
            }).then(res => {
                if(res.data.error){
                    {$this->onError}
                    $('#send-info').html('Ocorreu algum erro: ' + res.data.info.file[0]);
                }else{
                    {$this->onSuccess}

                    if({$this->as_type} == 0){
                        $("#{$this->field_id}").val(res.data.model.id);
                    }else if({$this->as_type} == 2){
                        $("#{$this->field_id}").val(res.data.model.url);
                    }
                    
                    toastr.success("{$this->success_message}");
                    $('#btn-upload-{$random}').hide();
                    $('.file-info-{$random}').addClass('d-none');
                    $('#{$this->field_name}').val(res.data.model.id);
                }
            }).catch(function (error) {
                {$this->onError}
                $('#send-info-{$random}').html('Ocorreu algum erro.');
            }).finally(function () {
                $('#send-info-{$random}').hide();
                $('#overlay-{$random}').hide();
            });

        }
      JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        if($this->preview !== null){
            $image = $this->preview;
        }else{
            $image = "{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=SELECT FILE";
        }

        $cut_label = Yii::t('app', 'Cut');
        $cancel_label = Yii::t('app', 'Cancel');
        $upload_label = Yii::t('app', 'Upload');
        $edit_label = Yii::t('app', 'Edit');

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
                        <img id="{$this->crop_preview}_crop" class="img-fluid rounded mb-2 mb-lg-0 " src="{$baseUrl}/dummy/code.php?x=150x150/fff/000.jpg&text=PREVIEW" style="width:100%;max-width:350px;">
                    </div>
                    <div class="modal-footer">
                    <a class="btn btn-warning crop-tool-{$random} d-none" href="javascript:;" id="btn-set-crop-{$random}"><i class="fas fa-cut"></i> {$cut_label} </a>
                    <a class="btn btn-danger crop-tool-{$random} d-none" href="javascript:;" id="btn-cancel-crop-{$random}"><i class="fas fa-window-close"></i> {$cancel_label} </span></a>
                    </div>
                </div>
                </div>
            </div>
        HTML;
        if($this->as_type == 1){
            $field = '<textarea class="d-none" type="text" id="'.$this->field_id.'" name="'.$this->field_name.'">'.$this->preview.'</textarea>';
        }else{
            $field = '<input class="d-none" type="text" id="'.$this->field_id.'" name="'.$this->field_name.'" value="'.$this->value.'" />';
        }
        $form_upload = <<< HTML
            {$modal}
            <div class="col-12 col-sm-auto mb-3">
                <div class="card border-success mb-3" id="preview-group-{$random}" >
                    <div id="overlay-{$random}" class="overlay" style="height: 100%;position: absolute;width: 100%;z-index: 3000;display:none;top:0;left:0;">
                        <div class="float-start d-block" id="file-progress-{$random}" style="width:60%;display:block;background:#333333;height:20px;position:relative;">
                            <i class="percent-send" style="position:absolute;left:50%;z-index:1">0%</i>
                            <div class="load-bar progress-bar-striped bg-success text-center" style="position:absolute;left:0;width:0%;height:20px;border-radius:3px;;z-index:0">
                            </div>
                        </div>
                    </div>
                    <div class="card-header">{$this->label}</div>
                    <div class="card-body center">

                        <div class="custom-file-upload center" id="custom-file-upload">
                            {$field}
                            <input type="file" id="{$this->file_element}" name="{$this->file_element}" accept="{$extensions}">
                            <img id="{$this->crop_preview}" class="img-fluid {$this->style_class} mb-2 mb-lg-0 " src="{$image}" />
                        </div>

                        <div class="text-center text-sm-left mb-2 mb-sm-0 file-info-{$random} d-none">
                            <div class="pt-sm-2 pb-1 mb-0 text-nowrap"><input class="form-control" id="file-description-{$random}" type="text" placeholder="File Description"></div>
                            <div class="text-muted"><small id="file-type-{$random}"></small></div>
                        </div>
                        
                    </div>

                    <div class="card-footer bg-transparent border-success btn-group file-info-{$random} d-none">
                        <a class="btn btn-warning d-none" href="javascript:;" id="btn-upload-{$random}"><i class="fas fa-upload"></i> {$upload_label}</a>
                        <a class="btn btn-success d-none" href="javascript:;" id="btn-start-crop-{$random}"><i class="fas fa-pencil"></i> {$edit_label}</a>
                        <a class="btn btn-danger href="javascript:;" id="btn-cancel-{$random}"><i class="fas fa-ban"></i> {$cancel_label}</a>
                    </div>

                    <div class="card-footer bg-transparent" style="display:none;" id="send-info-{$random}">
                    </div>
                </div>
            </div>


        HTML;
        echo $form_upload;
    }
}
