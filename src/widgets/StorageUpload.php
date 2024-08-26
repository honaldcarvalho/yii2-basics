<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\controllers\ControllerCommon;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use Yii;
use yii\web\View;
use yii\bootstrap5\Widget;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\File $model */
/** @var yii\widgets\ActiveForm $form */

class StorageUpload extends Widget
{
    public $token;
    public $random;
    public $folder_id = 1;
    public $ajax_grid_reload = '#list-files-grid';

    public function init()
    {
        parent::init();
        $this->token = ControllerCommon::User()->access_token;
        $this->random = Yii::$app->security->generateRandomString(10);
        PluginAsset::register(Yii::$app->view)->add(['axios']);
    }

    public function run()
    {
        $css = <<< CSS
            #progress-container-{$this->random} {
                width: 100%;
                background: #f3f3f3;
                border: 1px solid #ccc;
                border-radius: 4px;
                margin-top: 10px;
                height: 25px;
                position: relative;
            }
            #progress-bar-{$this->random} {
                height: 100%;
                background: #4caf50;
                width: 0%;
                transition: width 0.4s;
                border-radius: 4px;
            }
        CSS;

        \Yii::$app->view->registerCss($css);
        
        $script = <<< JS
            document.getElementById('upload-button-{$this->random}').addEventListener('click', () => {
                const file_input = document.getElementById('file-input-{$this->random}');
                const file = file_input.files[0];

                if (!file) {
                    alert('Please select a file first.');
                    return;
                }

                // Create a FormData object
                const formData = new FormData();
                formData.append('file', file);
                formData.append('folder_id', $this->folder_id);
                formData.append('save', 1);

                // Select the progress bar element
                const progressBar = document.getElementById('progress-bar-{$this->random}');
                const progressContainer = document.getElementById('progress-{$this->random}');
                const inputContainer = document.getElementById('input-{$this->random}');

                inputContainer.classList.add('d-none');
                progressContainer.classList.remove('d-none');

                axios.post('/rest/storage/send', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Authorization': `Bearer {$this->token}`
                    },
                    onUploadProgress: (progressEvent) => {
                        // Calculate the progress percentage
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        // Update the progress bar width
                        progressBar.style.width = `\${percentCompleted}%`;
                    }
                })
                .then(response => {
                    if(response.data.success){
                        toastr.success("File sended! " + response.data['message']);    
                    }else{
                        toastr.error("Error on send file! " + response.data['message']); 
                    }
                    progressBar.style.width = '0%';
                    $.pjax.reload({container: "{$this->ajax_grid_reload}", async: true,timeout : false});
                })
                .catch(error => {
                    toastr.error("Error on send file! " + response.data['message']);
                }).then(response => {
                    inputContainer.classList.remove('d-none');
                    progressContainer.classList.add('d-none');
                });

            });
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        $form_upload = <<< HTML

            <h1>Upload Blob File via AJAX</h1>
            <div class="row" id="input-{$this->random}">
                <div class="col-md-12">
                    <input type="file" id="file-input-{$this->random}" />
                    <button id="upload-button-{$this->random}">Upload</button>
                </div>
            </div>
            <div class="row d-none" id="progress-{$this->random}">
                <div id="progress-container-{$this->random}">
                    <div id="progress-bar-{$this->random}"></div>
                </div>
            </div>
        HTML;
        echo $form_upload;
    }
}