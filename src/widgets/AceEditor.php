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

    PluginAsset::register($view)->add(['ace']); // assume que você incluiu o `ace.js`

    $style = "width: 100%; height: {$this->height};";

    // Campo hidden sincronizado com o editor
    if ($this->hasModel()) {
        $input = Html::activeHiddenInput($this->model, $this->attribute, ['id' => "{$id}_hidden"]);
    } else {
        $input = Html::hiddenInput($this->name, $this->value, ['id' => "{$id}_hidden"]);
    }

    echo $input;
    echo Html::tag('div', Html::encode($this->value), ['id' => $id, 'style' => $style]);

    // Corrigir o nome da variável JS
    $varName = 'editor_' . str_replace(['-', '.'], '_', $id);

    $mode = $this->mode;
    $theme = $this->theme;
    $readOnly = $this->readOnly ? 'true' : 'false';

    $js = <<<JS
    var {$varName} = ace.edit("{$id}");
    {$varName}.setTheme("ace/theme/{$theme}");
    {$varName}.session.setMode("ace/mode/{$mode}");
    {$varName}.setReadOnly($readOnly);
    {$varName}.session.setValue(document.getElementById("{$id}_hidden").value);

    {$varName}.session.on('change', function(){
        document.getElementById("{$id}_hidden").value = {$varName}.getValue();
    });
    JS;

    $view->registerJs($js, View::POS_READY);
}

}
