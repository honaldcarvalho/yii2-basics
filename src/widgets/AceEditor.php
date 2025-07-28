<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;

class AceEditor extends InputWidget
{
    public $theme = 'monokai'; // tema padrão
    public $mode = 'php';      // linguagem padrão
    public $height = '400px';  // altura do editor
    public $readOnly = false;  // somente leitura
    public $clientOptions = [];

    public function run()
    {
        $id = $this->options['id'];
        $view = $this->getView();

        PluginAsset::register($view)->add(['ace']); // assume que você incluiu o `ace.js` em `ace/ace.js`

        $style = "width: 100%; height: {$this->height};";

        // campo hidden para manter sincronizado com o valor do editor
        if ($this->hasModel()) {
            $input = Html::activeHiddenInput($this->model, $this->attribute, ['id' => "{$id}_hidden"]);
        } else {
            $input = Html::hiddenInput($this->name, $this->value, ['id' => "{$id}_hidden"]);
        }

        echo $input;
        echo Html::tag('div', Html::encode($this->value), ['id' => $id, 'style' => $style]);

        $mode = Json::htmlEncode($this->mode);
        $theme = Json::htmlEncode($this->theme);
        $readOnly = $this->readOnly ? 'true' : 'false';

        $js = <<<JS
var editor_$id = ace.edit("$id");
editor_$id.setTheme("ace/theme/$theme");
editor_$id.session.setMode("ace/mode/$mode");
editor_$id.setReadOnly($readOnly);
editor_$id.session.setValue(document.getElementById("{$id}_hidden").value);

editor_$id.session.on('change', function(){
    document.getElementById("{$id}_hidden").value = editor_$id.getValue();
});
JS;

        $view->registerJs($js, View::POS_READY);
    }
}
