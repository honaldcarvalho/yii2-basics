<?php
namespace weebz\yii2basics\themes\weebz\widgets;

use Yii;
use yii\web\View;

class Upload extends \yii\bootstrap5\Widget
{
    public $ajaxURL = '';
    public $reloadGrid = [];
    public $fileField = 'file';
    public $type = ' image/*,.pdf,.doc,.docx,.xls,.xlsx';

    public function init()
    {
        $reload = '';
        if(array_count_values($this->reloadGrid) > 0){
            foreach ($this->reloadGrid as $item) {
                $reload.="$.pjax.reload({container: '#{$item}', async: false});";
            }
        }
        $script = <<<JS

            var files; 
            function removeFile(fileID){
                var item = '#item-' + fileID; 
                $( item ).fadeOut( 'slow', function() {
                    $(this).remove();
                });
                return false;
            }
            
            function handleSubmit(fileID){
                var item = '#item-' + fileID;  
                sendFile(fileID);
                $(item + ' .btn-enviar').remove();                
                return false;
            }

            $('#input-imagens').change(function(){
                $('#list-form').empty();
                files = this.files;
                for(var i = 0;i< this.files.length; i++) {
                    $('#list-form').append(addToQueue(i));
                    var imageTypes = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                    var item = '#item-' + i + ' .product-img';
                    var img = $(document.createElement('img'));
                    img.attr('width', '100');
                    
                    if (imageTypes.exec(this.files[i].name)) {
                        img.attr('src', URL.createObjectURL(this.files[i]));
                    }else{
                        img.attr('src','https://fakeimg.pl/350x200/?text=Documento');
                    }
                    img.appendTo(item);
                    if($('#auto-up').is(":checked")){
                        handleSubmit(i);
                    }
                }
            });

            // function addToQueue(i){
            //     var item = $('<div>', {id: 'item-'+ i, 'class': 'col-md-12'}); 
            //     item.append('<div class="image col-lg-2"></div>');
            //     item.append('<div class="progress col-lg-4"><div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div></div>');
            //     item.append('<div class="form-group col-lg-2"><button onclick="return handleSubmit('+ i +');" class="btn btn-info btn-enviar w-100">Enviar Arquivo</button><button class="btn btn-warning w-100" onclick="return removeFile('+ i +');">Remover</button></div>');
            //     item.append('<div class="status col-lg-4"></div>');
            //     return item;
            // }

            function addToQueue(i){
                var item = $('<div>', {id: 'item-'+ i, 'class': 'd-flex justify-content-between align-self-center item border-bottom pb-1 mb-1'}); 
                item.append('<div class="product-img"></div>');
                item.append('<div class="progress col-lg-6 align-self-center"><div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div></div>');
                item.append('<div class="button-group align-self-center"><a onclick="return handleSubmit('+ i +');" class="btn btn-success btn-enviar"><i class="fas fa-upload"></i></a><a class="btn btn-danger" onclick="return removeFile('+ i +');"><i class="fas fa-trash"></i></a></div>');
                //item.append('<div class="status col-lg-4"></div>');
                return item;
            }

            function sendFile(fileId) {
                var item = '#item-' + fileId;   
                var percent = $(item + ' .progress-bar');
                var status = $(item + ' .status');
                var data = new FormData();
                data.append('$this->fileField', files[fileId]);
                var ajaxUrl = '$this->ajaxURL';
                $.ajax({
                    xhr: function() {
                    var xhr = new window.XMLHttpRequest();

                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            var percentVal = percentComplete + '%';
                            percent.html(percentVal);
                            percent.css('width', percentVal);
                            if (percentComplete === 100) {
                            }
                        }
                    }, false);
                    return xhr;
                    },
                    url : ajaxUrl,
                    type : 'POST',
                    data : data,
                    contentType : false,
                    processData : false,
                    beforeSend: function() {
                        status.empty();
                        status.html('Enviando..');
                        var percentVal = '0%';
                        percent.html(percentVal);
                    },
                    complete: function(xhr,error) {
                    }
                }).done(function(response){
                    $( item ).fadeOut( 'slow', function() {
                        $(this).remove();
                    });
                }).fail(function(response){
                    percent.removeClass('bg-success');
                    percent.addClass('bg-danger');
                    status.html('<div class="alert alert-error" role="alert">'+response.responseJSON.message+'</div>');
                }).always(function(){
                    $reload
                });
            }           
        JS;
        
        $css = <<< CSS
            .flex-container {
                display: flex;
                align-items: stretch;
                background-color: #f1f1f1;
            }

            .flex-container > div {
                background-color: DodgerBlue;
                color: white;
                margin: 10px;
                text-align: center;
                line-height: 75px;
                font-size: 30px;
            }
            .label-file {
                padding: 15px 10px;
                width: 20%;

                text-align: center;
                display: block;
                margin-top: 10px;
                cursor: pointer;
                margin: 0 auto;
            }
            input[type=file] {
                display: none;
            }
        CSS;

        \Yii::$app->view->registerJs($script,View::POS_END);
        \Yii::$app->view->registerCss($css);

    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {   
        $html = <<<HTML
            <div class="file-form box box-primary w-100">
                <form class="form-anexar col-lg-12" id="form-anexar" method="post" enctype="multipart/form-data">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto-up">
                        <label class="form-check-label" for="auto-up">Envio Automático</label>
                    </div>
                    <div class="form-group field-noticia-imagem">
                        <label class="control-label label-file btn-weebz"><i class="fas fa-file-upload"></i> Selecionar Arquivos
                            <input type="file" accept="$this->type" id="input-imagens" multiple name="NoticiaArquivo[imagem]">
                        </label>
                    </div>   
                </form>
                <div class="row" id="list-form">
                </div>
            </div>
        HTML;

        echo $html;
    }
}