<?php

namespace weebz\yii2basics\modules\common\widgets;

use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap4\Widget;

/** @var yii\web\View $this */
/** @var weebz\yii2basics\modules\common\models\File $model */
/** @var yii\widgets\ActiveForm $form */

class UploadFile extends Widget
{
      /** FOR ALL BOOLEAN ATTRIBUTES
       *  1: true
     *    0: false
     */
    
    public $action = 'file/upload';
    public $save_file_model = 0;
    public $aspectRatio = '3/4';
    public $file_name = '';
    public $folder_id = 1;
    public $callback = '';
    public $auto = 0;
    public $field = '';
    public $extensions = ['jpg','png'];
    public $file_element = '#file-upload';
    public $show_file_element = 0;
    public $show_crop = 0;
    public $crop_preview = '#crop_preview_upload';
    public $onUploadProgress = "$('#send-info').html('Enviando...');";
    public $onError = "$('#send-info').html('Ocorreu algum erro: ' + res.data.info.file[0]);";
    public $onSuccess = '$("#send-info").html("Enviado");$("#btn-save").hide();$("$this->field").val(res.data.model.id);';

    public function init()
    {
        parent::init();
    }

    public function run()
    {
      $file_element = str_replace('#','',$this->file_element);
      $url = \Yii::getAlias('@web');
      $csrfToken = \Yii::$app->getRequest()->getCsrfToken();
      $extensions = '"'.implode('","',$this->extensions).'"';

      \Yii::$app->view->registerJsFile(
        "$url/plugins/axios/axios.min.js"
      );

      if($this->show_crop){
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
      }

      $script = <<< JS
      
      var editing = false;
      var cropperImage = null;
      var image = null;
      var imageCroped = null;
      var preview = $("$this->crop_preview");
      var file = null;

      function startCrop() {
          $('.crop-tool').removeClass('d-none');
          $('#btn-start-crop').addClass('d-none');
          image = $('$this->crop_preview');
          image.cropper({
            aspectRatio: $this->aspectRatio,
            crop: function (event) { }
          });
          cropperImage = image.data('cropper');
      }
      
      function cancelCrop() {
          $('.crop-tool').addClass('d-none');
          $('#btn-start-crop').removeClass('d-none');
          image.cropper("destroy");
      }
      
      function setCrop() {
      
          $('.crop-tool').addClass('d-none');
          $('#btn-start-crop').removeClass('d-none');
          var canvas = cropperImage.getCroppedCanvas();
          var data = canvas.toDataURL();
          preview.attr('src',data);
          image.cropper("destroy");
      
          canvas.toBlob(function (blob) {
              var file = new File([blob], "imagem.jpg", {type: "image/jpeg", lastModified: new Date().getTime()});
              var container = new DataTransfer();
              container.items.add(file);
              var fileField = document.getElementById('file-upload');
              fileField.files = container.files;
          });
      
      }
      
      $(document).ready(function(){
          var file_element = document.getElementById('$file_element');
          if($this->auto == 1){
            $('$this->file_element').on("change", function(){ file = file_element.files[0]; upload(); });
          }else{
            $('$this->file_element').on("change", function(){ file = file_element.files[0]; encodeImageFileAsURL(file_element); });
          }

          $('#btn-start-crop').click(function () {
            if (editing === false) {
              startCrop();
              editing = true;
            }
          });
      
          $('#btn-cancel-crop').click(function () {
            if (editing) {
              cancelCrop();
              editing = false;
            }
          });
      
          $('#btn-set-crop').click(function () {
            if (editing) {
              setCrop();
              editing = false;
            }
          });
      
      }); 
      
      function encodeImageFileAsURL(element) {

          $('#btn-save').show();

          var reader = new FileReader();
          if(file.type == 'image/jpeg' || file.type == 'image/png' ||file.type == 'image/jpg' ){
              reader.onloadend = function () {

                preview.attr('src',reader.result);
                preview.css('display','block');

                $('#preview-group').removeClass('d-none');

              }
              reader.readAsDataURL(file);
          }else{
              preview.css('display','none');
              $('#preview-group').addClass('d-none');
          }
      }

      $('#btn-save').on('click', function(){
          upload();     
      });
      
      function upload(){
        var extensions = [$extensions];

        var formData = new FormData();
        formData.set('_csrf', '$csrfToken');
        formData.set('UploadForm[file]', file);
        formData.set('UploadForm[file_name]', "$this->file_name");
        formData.set('UploadForm[folder_id]', $this->folder_id);
        formData.set('UploadForm[description]', file.name);
        formData.set('UploadForm[save_file_model]', $this->save_file_model);
        formData.set('UploadForm[extensions]', extensions);
        
        axios.post("$url/$this->action", formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: progressEvent => {
                $this->onUploadProgress  
            }
        }).then(res => {
            if(res.data.error){
              $this->onError
            }else{
              $this->onSuccess
            }
        }).catch(function (error) {
            $this->onError
        });

      }
      JS;
      
      \Yii::$app->view->registerJs($script, View::POS_END);
      
        $crop = '
          <div class="form-group d-none" id="preview-group">
            <label class="control-label">Preview</label>
            <img src="" id="'.str_replace('#','',$this->crop_preview).'" style="display:none; width: auto;height:350px;">
            <a class="btn btn-warning" href="javascript:;" id="btn-save">Salvar</a>
            <a class="btn btn-success" href="javascript:;" id="btn-start-crop">Editar</a>
            <a class="btn btn-warning crop-tool d-none" href="javascript:;" id="btn-set-crop">Cortar</a>
            <a class="btn btn-warning crop-tool d-none" href="javascript:;" id="btn-cancel-crop">Cancelar</span></a>
            <span class="control-label" id="send-info" ></label>
          </div>';

        if($this->show_crop == 1) echo $crop;

        if($this->show_file_element == 1){
          echo Html::input('text', 'file_description', '', []);
          echo Html::input('file', $file_element, '', ['id'=>$file_element,"accept"=>".".implode(',.',$this->extensions)]);
        }
    }

}