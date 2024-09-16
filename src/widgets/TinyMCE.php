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
  /**
   * @var string the language to use. Defaults to null (en).
   */
  public $language;
  /**
   * @var array the options for the TinyMCE JS plugin.
   * Please refer to the TinyMCE JS plugin Web page for possible options.
   * @see http://www.tinymce.com/wiki.php/Configuration
   */
  public $clientOptions = [];


  public function init()
  {
    parent::init();
    $view = $this->getView();
    $pluginAssets = PluginAsset::register($view)->add(['tinymce']);
    $this->baseUrl = $pluginAssets->baseUrl;
  }

  /**$
   * {@inheritdoc}
   */
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
    $js[] = "tinymce.remove('#$id');tinymce.init($options);";
    $view->registerJs(implode("\n", $js));
  }
}
