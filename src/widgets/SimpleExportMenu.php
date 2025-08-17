<?php
namespace weebz\yii2basics\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/** Menu de exportação simples (CSV, XML, JSON) 
 * 
 USAGE:
 echo SimpleExportMenu::widget([
    'label'   => Yii::t('app','Export All Data'),
    'route'   => ['index'],                          // mesma action
    'params'  => Yii::$app->request->get(),          // mantém filtros
    'formats' => ['csv'=>true, 'xml'=>true, 'json'=>true],
    'buttonOptions' => ['class' => 'btn btn-success'],
]);
*/
class SimpleExportMenu extends Widget
{
    /** Texto do botão */
    public string $label = 'Export';

    /** Rota base. Ex: ['invoice/index'] (default: rota atual) */
    public $route;

    /** Parâmetros GET a manter (default: $_GET atual) */
    public array $params = [];

    /** Quais formatos exibir */
    public array $formats = ['csv' => true, 'xml' => true, 'json' => false];

    /** Opções do botão */
    public array $buttonOptions = ['class' => 'btn btn-success'];

    /** Opções do container btn-group */
    public array $containerOptions = ['class' => 'btn-group'];

    public function init(): void
    {
        parent::init();
        if ($this->route === null) {
            $this->route = [Yii::$app->controller->route];
        }
        if (empty($this->params)) {
            $this->params = Yii::$app->request->get();
        }
        // garante atributo BS5 e não PJAX
        $this->buttonOptions['data-bs-toggle'] = $this->buttonOptions['data-bs-toggle'] ?? 'dropdown';
        $this->buttonOptions['data-pjax'] = 0;
        $this->containerOptions['data-pjax'] = 0;
    }

    public function run(): string
    {
        $id = $this->getId();
        $btn = Html::button($this->label . ' ▾', array_merge([
            'class' => 'btn btn-success dropdown-toggle',
            'id' => $id . '-btn',
        ], $this->buttonOptions));

        $items = [];
        foreach (['csv' => 'CSV', 'xml' => 'XML', 'json' => 'JSON'] as $key => $text) {
            if (!empty($this->formats[$key])) {
                $url = Url::to(array_merge($this->route, array_merge($this->params, ['export' => $key])));
                $items[] = Html::a($text, $url, [
                    'class' => 'dropdown-item',
                    'data-pjax' => 0, // fundamental p/ não virar XHR
                ]);
            }
        }
        $menu = Html::tag('div', implode("\n", $items), ['class' => 'dropdown-menu dropdown-menu-end']);

        return Html::tag('div', $btn . $menu, $this->containerOptions);
    }
}
