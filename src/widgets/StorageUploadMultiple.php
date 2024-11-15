<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\controllers\AuthController;
use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use Yii;
use yii\web\View;
use yii\bootstrap5\Widget;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\File $model */
/** @var yii\widgets\ActiveForm $form */

/**
 * <?= StorageUpload::widget([
 *      'folder_id' => $model->id, //Folder model id
 *      'grid_reload'=>1, //Enable auto reload GridView. disable = 0; enable = 1;
 *      'grid_reload_id'=>'#list-files-grid', //ID of GridView will reload
 *     ]); ?>
 * 
 * Attact file to model
    <?= StorageUploadMultiple::widget([
    'group_id' => AuthController::userGroup(),
    'attact_model'=>[
        'class_name'=> 'weebz\\yii2basics\\models\\PageFile',
        'id'=> $model->id,
        'fields'=> ['page_id','file_id']
    ],
    'grid_reload'=>1,
    'grid_reload_id'=>'#list-files-grid'
    ]); ?>
 */
class StorageUploadMultiple extends Widget
{
    public $token;
    public $random;

    /** Folder model id */
    public $thumb_aspect = 1;
    /** Folder model id */
    public $folder_id = 1;
    /** Folder group id */
    public $group_id = 1;
    /** Model name to attact files */
    public $attact_model = 0;
    /** Model id to attact files */
    public $grid_reload = 0;
    /** ID of GridView will reload */
    public $grid_reload_id = '#list-files-grid';

    public function init()
    {
        parent::init();
        $this->attact_model = json_encode($this->attact_model);
        $this->token =  AuthController::User()->access_token;
        $this->random =  ControllerCommon::generateRandomString(6);
        PluginAsset::register(Yii::$app->view)->add(['axios','jquery-cropper']);
    }

    public function run()
    {
        $css = <<< CSS

            #progress-bar-{$this->random} {
                height: 100%;
                width: 0%;
                transition: width 0.4s;
                border-radius: 4px;
            }
            
            .card-info {
                display: flex;
                flex-direction: row;
                width: 100%;
                height: 250px;
                max-width: 100%;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .card-content {
                flex: 1;
                padding: 20px;
            }

            .card-content ul {
                list-style-type: none;
                padding: 0;
            }

            .card-content ul li {
                margin-bottom: 10px;
                font-size: 16px;
            }

            .light-mode .card-content ul li {
                color:#333;
            }

            .dark-mode .card-content ul li {
                color:#fff;
            }

            .card-image {
                width: 50%;
                max-width: 250px;
                overflow: hidden;
            }

            .card-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            @media (max-width: 768px) {
                .card-info {
                    flex-direction: column;
                    max-width: 100%;
                }

                .card-image {
                    width: 100%;
                    max-height: 300px;
                }
            }

            .table * {
                vertical-align: middle !important;
            }
        CSS;

        \Yii::$app->view->registerCss($css);

        $script = <<< JS
        
            var id_{$this->random} = 0;

            function el(id){
                return document.getElementById(id);
            }

            var count = 0;
            var total_files;
            let temp_image;
            let file_input = el("file-input-{$this->random}");
            let table_files = el('table-files-{$this->random}');
            let input_container = el('input-{$this->random}');
            let upload_button = el('upload-button-{$this->random}');
            let removeList = [];

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                var k = 1024; // Define the constant for kilobyte
                var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB']; // Units
                var i = Math.floor(Math.log(bytes) / Math.log(k)); // Determine the unit index
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function isImage(file){
                var fileType = file.type;
                var validImageTypes = ["image/jpeg", "image/png", "image/gif", "image/bmp", "image/webp"];
                if (validImageTypes.includes(fileType)) {
                    return true;
                }
                return false;
            }

            async function encodeImageFileAsURL(file,preview) {
                return new Promise((resolve, reject) => {
                    var reader = new FileReader();            
                    reader.onloadend = function () {
                        preview.src = reader.result;
                        resolve(); // Resolving the promise when the encoding is complete
                    };
                    reader.onerror = reject; // Rejecting the promise in case of an error
                    reader.readAsDataURL(file);
                });
            }

            function removeFile(index) {

                var filesArray = Array.from(file_input.files);
                filesArray.splice(index, 1);
                var dataTransfer = new DataTransfer();
                filesArray.forEach(file => dataTransfer.items.add(file));
                file_input.files = dataTransfer.files;
                var event = new Event('change');
                file_input.dispatchEvent(event);

                if(file_input.files.length == 0){
                    table_files.innerHTML = '';
                    upload_button.disabled = true;
                }
                console.log('Remove:' + index);
            }

            function upload(index,multiple){
                var i = index;
                var file = file_input.files[index];

                var progressBar = el(`progress-bar-\${index}-{$this->random}`);
                progressBar.style.width = '0%';
                var uploadButton = el(`btn-upload-\${index}-{$this->random}`);

                var formData = new FormData();
                formData.append('file', file);
                formData.append('folder_id', $this->folder_id);
                formData.append('group_id', $this->group_id);
                formData.append('attact_model',JSON.stringify($this->attact_model));
                formData.append('thumb_aspect', "{$this->thumb_aspect}");
                formData.append('save', 1);

                let button = $(`#btn-upload-\${index}-{$this->random}`);
                let old_class = button.children("i").attr('class');
                button.prop('disabled',true);
                object = button.children("i");
                object.removeClass(old_class);
                object.addClass('fas fa-sync fa-spin m-2');

                axios.post('/rest/storage/send', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Authorization': `Bearer {$this->token}`
                    },
                    onUploadProgress: (progressEvent) => {
                        var percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        progressBar.style.width = `\${percentCompleted}%`;
                        if(percentCompleted == 100){
                            progressBar.textContent  = 'processing... await...';                        
                        } else {
                            progressBar.textContent  = progressBar.style.width;                        
                        }
                        
                    }
                })
                .then((response) => {
                    if(response.data.success){
                        toastr.success(`File \${response.data.data.description} sended!`);
                        if(!multiple){
                            removeFile(index);
                        } else {
                            count++;
                            if(count == total_files){
                                file_input.value = '';
                            }
                            el("row_" + i).remove();
                        }
                        
                    }else{
                        
                        var send_error = response.data.data;
                        var erros = '';
                        if(send_error.file) {
                            Object.keys(send_error.file).forEach(key => {
                                erros += send_error.file[key];
                            });
                        } else {
                            erros = 'unknown error';
                        }
                        progressBar.style.width = `0%`;
                        progressBar.textContent  = `0%`;   
                        toastr.error(`Error on send file: \${erros}! `); 
                        uploadButton.disabled = false;
                    }

                    if({$this->grid_reload} == 1){
                        $.pjax.reload({container: "{$this->grid_reload_id}", async: true,timeout : false});
                    }
                })
                .catch(error => {
                    toastr.error("Error on page! " + error);
                })
                .finally((response) => {
                    //progressBar.textContent = response.data.message;
                    button.prop('disabled',false);
                    object.removeClass('fas fa-sync fa-spin m-2');
                    object.attr('class',old_class);
                });
            }

            file_input.addEventListener('change', async function(event) {

                var files = event.target.files;
                total_files = files.length;

                if (files.length > 0) {
                    
                    upload_button.disabled = false;
                    table_files.innerHTML = '';

                    Array.from(file_input.files).forEach(async (file, index) => {

                        let upload_button = document.createElement("button");
                        upload_button.id = `btn-upload-\${index}-{$this->random}`;
                        upload_button.classList.add('btn', 'btn-warning');
                        upload_button.innerHTML = '<i class="fas fa-upload m-2"></i>';
                        upload_button.onclick = function() {
                            upload(index,false);
                        };

                        var remove_button = document.createElement('button');
                        remove_button.classList.add('btn', 'btn-danger');
                        remove_button.innerHTML = '<i class="fas fa-trash m-2"></i>';
                        remove_button.onclick = function() {
                            removeFile(index);
                        };

                        let progress_container = document.createElement("div");
                        progress_container.classList.add('progress');
                        progress_container.style.width = '300px';
                        
                        let progress_bar = document.createElement("div");
                        progress_bar.id = `progress-bar-\${index}-{$this->random}`;
                        progress_bar.classList.add('progress-bar', 'progress-bar-striped', 'bg-success', 'progress-bar-animated');
                        progress_container.append(progress_bar);

                        let preview = document.createElement("img");
                        preview.style.width = '100px';

                        if(isImage(file)){
                            await encodeImageFileAsURL(file,preview);
                        }else{
                            preview.src = '/dummy/code.php?x=150x150/fff/000.jpg&text=NO PREVIEW';
                        }

                        var row = table_files.insertRow();
                        var cellImage =    row.insertCell(0);
                        var cellName =     row.insertCell(1);
                        var cellProgress = row.insertCell(2);
                        var cellSize =     row.insertCell(3);
                        var cellType =     row.insertCell(4);
                        var cellActions =  row.insertCell(5);

                        row.id = "row_" + index;

                        cellProgress.append(progress_container);

                        cellImage.append(preview);
                        cellName.textContent = file.name;
                        cellName.classList.add('align-middle');
                        cellSize.textContent = formatFileSize(file.size); // Convert size to KB
                        cellType.textContent = file.type || 'N/A'; // Handle cases where type is unavailable
                        cellActions.append(upload_button);
                        cellActions.append(remove_button);
                        
                    });
                    table_files.classList.remove('d-none');
                }
                
            });

            el('upload-button-{$this->random}').addEventListener('click', (e) => {
                count = 0;
                el('upload-button-{$this->random}').disabled = true;
                Array.from(file_input.files).forEach((file, index) => {
                    upload(index,true);
                });

            });
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        $form_upload = <<< HTML

            <div class="btn-group mt-2" role="group" id="input-{$this->random}">
                <button class="btn btn-info position-relative">
                    <input type="file" multiple="true" class="position-absolute z-0 opacity-0 w-100 h-100"  id="file-input-{$this->random}" aria-label="Upload">
                    <i class="fas fa-folder-open m-2"></i> Select File
                </button>
                <button class="btn btn-warning" id="upload-button-{$this->random}" disabled="true"> <i class="fas fa-upload m-2"></i> Upload</button>
            </div>

            <table class="table" id="table-files-{$this->random}">
                <tbody>
                </tbody>
            </table>
        HTML;
        echo $form_upload;
    }
}
