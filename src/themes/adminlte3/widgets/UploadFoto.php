<?php

namespace weebz\yii2basics\themes\adminlte3\widgets;

use Yii;
use yii\web\View;

class UploadFoto extends \yii\bootstrap5\Widget
{
  public $aspectRatio = '35/22';
  public $reloadGrid = [];
  public $fileField = 'file';
  public $imagem = 'https://avatars0.githubusercontent.com/u/3456749"';
  public $type = ' image/*,.pdf,.doc,.docx,.xls,.xlsx';
  public $view;
  
  
  public function init()
  {
      \Yii::$app->view->registerJsFile(\weebz\yii2basics\controllers\ControllerCommon::getAssetsDir() . '/plugins/cropper/cropper.min.js');
      \Yii::$app->view->registerCssFile(\weebz\yii2basics\controllers\ControllerCommon::getAssetsDir() . '/plugins/cropper/cropper.min.css');
  }

  /**$
   * {@inheritdoc}
   */
  public function run()
  {
    $reload = '';
    $baseUrl = Yii::$app->params['rootUrl'];
    if (array_count_values($this->reloadGrid) > 0) {
      foreach ($this->reloadGrid as $item) {
        $reload .= "$.pjax.reload({container: '#{$item}', async: false});";
      }
    }

    $script = <<<JS
            
      window.addEventListener('DOMContentLoaded', function () {

        var banner = document.getElementById('photo_x');
        var image = document.getElementById('image_x');
        var input = document.getElementById('image_upload_x');
        var arquivo = document.getElementById('$this->fileField');
        var modal = $('#modal');
        var cropper;

        $('[data-toggle="tooltip"]').tooltip();

        input.addEventListener('change', function (e) {
          var files = e.target.files;
          var done = function (url) {
            input.value = '';
            image.src = url;
            modal.modal('show');
          };
          var reader;
          var file;
          var url;

          if (files && files.length > 0) {
            file = files[0];

            if (URL) {
              done(URL.createObjectURL(file));
            } else if (FileReader) {
              reader = new FileReader();
              reader.onload = function (e) {
                done(reader.result);
              };
              reader.readAsDataURL(file);
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
                    let file = new File([blob], "imagem.jpg", {type: "image/jpeg", lastModified: new Date().getTime()});
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
            width: 40%;

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
    \Yii::$app->view->registerJs($script,View::POS_END);

    $html = <<<HTML
        <div class="row">
            <div class="col-sm-12 text-center pb-2">
                <img class="rounded" id="photo_x" src="$this->imagem" style="width:400px" alt="banner">
            </div>
            <div class="col-sm-12">
                <label class="label-file btn-weebz" data-toggle="tooltip" title="Selecione a Imagem">
                   <i class="fas fa-file-upload"></i> Selecione a Imagem
                   <input type="file" class="sr-only" id="image_upload_x" name="image_upload_x" accept="image/*">
                </label>
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
