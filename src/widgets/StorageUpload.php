<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\web\View;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\Widget;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\models\File $model */
/** @var yii\widgets\ActiveForm $form */

class StorageUpload extends Widget
{
    public $random;

    public function init()
    {
        parent::init();
        $this->random = Yii::$app->security->generateRandomString(10);
    }

    public function run()
    {

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

                // Send the file using fetch
                fetch('/storage/send', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    console.log('Success:', result);
                    alert('Success:', result);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error:', error);
                });
            });
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);

        $form_upload = <<< HTML

            <h1>Upload Blob File via AJAX</h1>
            <input type="file" id="file-input-{$this->random}" />
            <button id="upload-button-{$this->random}">Upload</button>

            <script src="upload.js"></script>

        HTML;
        echo $form_upload;
    }
}