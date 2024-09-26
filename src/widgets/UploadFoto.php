<?php

namespace weebz\yii2basics\widgets;

use weebz\yii2basics\controllers\rest\ControllerCustom;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;
use Yii;
use yii\web\View;

class UploadFoto extends \yii\bootstrap5\Widget
{
  public $aspectRatio = '35/22';
  public $reloadGrid = [];
  public $fileField = 'file';
  public $fileName = 'file';
  public $imagem = '';
  public $type = ' image/*';
  public $view;


  public function init()
  {
    PluginAsset::register(Yii::$app->view)->add(['cropper']);
  }

  /**$
   * {@inheritdoc}
   */
  public function run()
  {
    $showRemove = '';
    $assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist');
    if (empty($this->imagem)) {
      $this->imagem = $assetDir . '/img/no-image.png';
      $showRemove = 'd-none';
    }

    $reload = '';
    if (array_count_values($this->reloadGrid) > 0) {
      foreach ($this->reloadGrid as $item) {
        $reload .= "$.pjax.reload({container: '#{$item}', async: false});";
      }
    }

    $script = <<<JS
    /**
     * Compress an image to be smaller than the max file size using Canvas API.
     * @param {File} file - The image file to compress.
     * @param {number} maxSize - The maximum file size in bytes.
     * @returns {Promise<File>} A promise that resolves with the compressed image file.
     */
    function compressImage(file, maxSize) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();

        // Load the image file
        reader.readAsDataURL(file);
        reader.onload = (event) => {
          const img = new Image();
          img.src = event.target.result;

          img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Set canvas dimensions to the image dimensions
            let width = img.width;
            let height = img.height;

            // Scale down the image dimensions if needed
            const maxDimension = 1000; // Max dimension (width or height) after scaling
            if (width > maxDimension || height > maxDimension) {
              if (width > height) {
                height = Math.floor((height * maxDimension) / width);
                width = maxDimension;
              } else {
                width = Math.floor((width * maxDimension) / height);
                height = maxDimension;
              }
            }

            // Set the canvas size and draw the scaled image
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            // Get the compressed image data
            canvas.toBlob((blob) => {
              // Ensure the compressed image size is below the maxSize
              const compressedFile = new File([blob], file.name, {
                type: file.type,
                lastModified: Date.now()
              });
              resolve(compressedFile); // Return the compressed file

            }, file.type, 0.8); // Adjust the quality (0.8 is 80%)
          };

          img.onerror = (error) => {
            reject('Error loading image: ' + error);
          };
        };

        reader.onerror = (error) => {
          reject('Error reading file: ' + error);
        };
      });
    }

    function encodeImageFileAsURL(file,preview) {
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

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024; // Define the constant for kilobyte
      const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB']; // Units
      const i = Math.floor(Math.log(bytes) / Math.log(k)); // Determine the unit index
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function isImage(file) {
      const fileType = file.type;
      const validImageTypes = ["image/jpeg", "image/png", "image/gif", "image/bmp", "image/webp"];
      if (validImageTypes.includes(fileType)) {
        return true;
      }
      return false;
    }

    window.addEventListener('DOMContentLoaded', function () {

      var banner = document.getElementById('photo_x');
      var remove = document.getElementById('remove');
      var btn_remove = document.getElementById('btn-remove');
      var image = document.getElementById('image_x');
      var input = document.getElementById('image_upload_x');
      var arquivo = document.getElementById('$this->fileField');
      var modal = $('#modal');
      var cropper;

      $('[data-toggle="tooltip"]').tooltip();

      btn_remove.addEventListener('click', function (e) {
        banner.src = '{$assetDir}/img/no-image.png';
        arquivo.files = null;
        remove.value = 1;
      });

      input.addEventListener('change', function (e) {
        var files = e.target.files;

        if (files && files.length > 0) {
          file = files[0];
          const maxSizeInBytes = 5 * 1024 * 1024;
          if (files[0].type == 'image/png' && files[0].size > maxSizeInBytes) {
            alert('Image exceeds 5MB limit even after compression.');
            return false;
          } else if(files[0].type == 'image/png') {
            encodeImageFileAsURL(file,image).then((blob) => {
              modal.modal('show');
            }).catch((error) => {
              console.log(error);
              return false;
            });
          }else{
            compressImage(files[0]).then((blob) => {
              let file_compressed = new File([blob], "imagem.jpg", { type: "image/jpeg", lastModified: new Date().getTime() });
              let container = new DataTransfer();
              container.items.add(file_compressed);
              arquivo.files = container.files;
              image.src = URL.createObjectURL(blob);
              modal.modal('show');
              console.log(image.src);
              console.log(formatFileSize(blob.size));
              return true;
            }).catch((error) => {
              console.log(error);
              return false;
            });
          }
        }

      });

      modal.on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
          initialAspectRatio: $this->aspectRatio,
          aspectRatio: $this->aspectRatio,
          viewMode: 2,
        });
      });

      document.getElementById('cancelar').addEventListener('click', function () {
        cropper.destroy();
        cropper = null;
      });

      document.getElementById('crop').addEventListener('click', function () {
        var initialAvatarURL;
        var canvas;

        if (cropper) {
          canvas = cropper.getCroppedCanvas();
          initialAvatarURL = banner.src;
          banner.src = canvas.toDataURL();
          canvas.toBlob(function (blob) {
            let file = new File([blob], "imagem.jpg", { type: "image/jpeg", lastModified: new Date().getTime() });
            let container = new DataTransfer();
            container.items.add(file);
            arquivo.files = container.files;
          });
        }
        cropper.destroy();
        cropper = null;
        modal.modal('hide');
      });
    });
    JS;

    $css = <<< CSS
        .label-file {
            padding: 20px 10px;
            width: 30%;
            text-align: center;
            display: block;
            margin-top: 10px;
            cursor: pointer;
            margin: 0 auto;
        }
        input[type=file] {
            display: none;
        }
        .img-container img {
          width: 100%;
          max-height: 80vh;
        }
        #photo{
            width: 60%;
            margin: 0 auto;
            padding: 5px;
        }
    CSS;

    \Yii::$app->view->registerCss($css);
    \Yii::$app->view->registerJs($script, View::POS_END);

    $html = <<<HTML
        <div class="row">
            <div class="col-sm-12 text-center pb-2">
                <img class="rounded" id="photo_x" src="$this->imagem" style="width:400px" alt="banner">
            </div>
            <div class="col-md-12 text-center pb-2">
                <label class="label-file w-10 btn-weebz" data-toggle="tooltip" title="Selecione a Imagem">
                   <i class="fas fa-file-upload"></i> Selecione a Imagem
                   <input type="file" class="sr-only" id="image_upload_x" name="image_upload_x" accept="image/*">
                   <input type="file" class="sr-only" id="$this->fileField" name="$this->fileName" accept="image/*">
                   <input type="hidden" id="remove" name="remove" value="0">
                </label>
                <a href="javascript:;" id="btn-remove" class="btn label-file w-10 btn-danger {$showRemove}"><i class="fas fa-trash"></i> Remover</a>
            </div>
        </div>
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Cortar imagem</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body" style="height:500px!importante">
                <div class="img-container">
                  <img id="image_x" src="https://avatars0.githubusercontent.com/u/3456749">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelar" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="crop">Crop</button>
              </div>
            </div>
          </div>
        </div>
    HTML;
    echo $html;
  }
}