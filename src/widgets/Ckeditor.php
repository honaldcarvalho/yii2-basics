<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;

class Ckeditor extends InputWidget
{
    public $language;
    public $clientOptions = [];

    public function init(): void
    {
        parent::init();

        $view = $this->getView();
        PluginAsset::register($view)->add(['ckeditor5']);

        $textBig = <<<HTML
<h3>The standard Lorem Ipsum passage, used since the 1500s</h3>
<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua..."</p>
<h3>Section 1.10.32 of "de Finibus Bonorum et Malorum"</h3>
<p>"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium..."</p>
HTML;

        $this->clientOptions = array_merge([
            'toolbar' => [
                'heading',
                '|',
                'bold',
                'italic',
                'link',
                'bulletedList',
                'numberedList',
                '|',
                'insertTable',
                'blockQuote',
                'undo',
                'redo',
                '|',
                'loremIpsumSmall',
                'loremIpsumBig'
            ]
        ], $this->clientOptions);

        $view->registerJs(<<<JS
if (!window.CKEDITOR_LOADED) {
    window.CKEDITOR_LOADED = true;

    class LoremIpsumSmallPlugin {
        constructor(editor) {
            this.editor = editor;
        }
        init() {
            this.editor.ui.componentFactory.add('loremIpsumSmall', locale => {
                const view = new window.CKEDITOR5.ButtonView(locale);
                view.set({
                    label: 'Lorem Small',
                    withText: true,
                    tooltip: 'Inserir Lorem pequeno'
                });
                view.on('execute', () => {
                    this.editor.model.change(writer => {
                        this.editor.model.insertContent(writer.createText('Lorem ipsum dolor sit amet, consectetur adipiscing elit.'));
                    });
                });
                return view;
            });
        }
    }

    class LoremIpsumBigPlugin {
        constructor(editor) {
            this.editor = editor;
        }
        init() {
            this.editor.ui.componentFactory.add('loremIpsumBig', locale => {
                const view = new window.CKEDITOR5.ButtonView(locale);
                view.set({
                    label: 'Lorem Big',
                    withText: true,
                    tooltip: 'Inserir Lorem grande'
                });
                view.on('execute', () => {
                    this.editor.model.change(writer => {
                        this.editor.model.insertContent(writer.createText(`${textBig}`));
                    });
                });
                return view;
            });
        }
    }

    window.LoremIpsumSmallPlugin = LoremIpsumSmallPlugin;
    window.LoremIpsumBigPlugin = LoremIpsumBigPlugin;
}
JS, View::POS_HEAD);
    }

    public function run(): void
    {
        $view = $this->getView();
        $id = $this->options['id'];

        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->options);
        }

        $options = Json::encode(array_merge($this->clientOptions, [
            'extraPlugins' => ['LoremIpsumSmallPlugin', 'LoremIpsumBigPlugin'],
        ]));

        $view->registerJs(<<<JS
if (window.ClassicEditor) {
    ClassicEditor
        .create(document.querySelector('#{$id}'), {$options})
        .catch(error => console.error(error));
}
JS);
    }
}
