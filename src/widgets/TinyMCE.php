<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;

class TinyMCE extends InputWidget
{
    public $baseUrl;
    public $language;
    public $clientOptions = [];

    public function init()
    {
        parent::init();
        $view = $this->getView();
        $pluginAssets = PluginAsset::register($view)->add(['tinymce']);
        $this->baseUrl = $pluginAssets->baseUrl;

        // Adicionando o botão padrão "Lorem Ipsum" na configuração
        $this->clientOptions = array_merge([
            'plugins' => [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
                'table', 'emoticons', 'template', 'help'
            ],
            'toolbar' => "undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | " .
                         "bullist numlist outdent indent | link image loremIpsum| print preview media fullscreen | " .
                         "forecolor backcolor emoticons ",
            'setup' => new \yii\web\JsExpression('function(editor) {
                editor.ui.registry.addButton("loremIpsum", {
                    text: "Lorem Ipsum",
                    icon: "edit-block",
                    tooltip: "Inserir texto Lorem Ipsum",
                    onAction: function () {
                        let loremText = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
                        editor.insertContent(loremText);
                    }
                });
            }')
        ], $this->clientOptions);
    }

    public function run()
    {
        $js = [];
        $view = $this->getView();
        $id = $this->options['id'];

        $this->clientOptions['selector'] = "#$id";

        if ($this->language !== null && $this->language !== 'en-US') {
            $this->clientOptions['language'] = strtolower(str_replace('-', '_', $this->language));
        }

        $options = Json::encode($this->clientOptions);

        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->options);
        }

        $js[] = "tinymce.remove('#$id'); tinymce.init($options);";
        $view->registerJs(implode("\n", $js));
    }
}
