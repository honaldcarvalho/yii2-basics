<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\web\View;

class WidgetModel extends \yii\bootstrap5\Widget
{

  public function init()
  {

  }

  /**$
   * {@inheritdoc}
   */
  public function run()
  {

    $script = <<<JS
    JS;

    $css = <<< CSS
    CSS;

    \Yii::$app->view->registerCss($css);
    \Yii::$app->view->registerJs($script,View::POS_END);

    $html = <<<HTML
    HTML;

    echo $html;
  }
}
