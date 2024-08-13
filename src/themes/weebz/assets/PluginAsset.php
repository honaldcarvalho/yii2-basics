<?php
namespace weebz\yii2basics\modules\common\themes\weebz\assets;

use yii\web\AssetBundle;

class PluginAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/common/themes/weebz/web/dist/plugins';

    public $depends = [
        'weebz\yii2basics\modules\common\themes\weebz\assets\BaseAsset'
    ];

    public static $pluginMap = [
        'fontawesome' => [
            'css' => 'fontawesome-free/css/all.min.css'
        ],
        'icheck-bootstrap' => [
            'css' => ['icheck-bootstrap/icheck-bootstrap.css']
        ],
        'sweetalert2' => [
            'css' => 'sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
            'js' => 'sweetalert2/sweetalert2.min.js'
        ],
    ];

    /**
     * add a plugin dynamically
     * @param $pluginName
     * @return $this
     */
    public function add($pluginName)
    {
        $pluginName = (array) $pluginName;

        foreach ($pluginName as $name) {
            $plugin = $this->getPluginConfig($name);
            if (isset($plugin['css'])) {
                foreach ((array) $plugin['css'] as $v) {
                    $this->css[] = $v;
                }
            }
            if (isset($plugin['js'])) {
                foreach ((array) $plugin['js'] as $v) {
                    $this->js[] = $v;
                }
            }
        }

        return $this;
    }

    /**
     * @param $name plugin name
     * @return array|null
     */
    private function getPluginConfig($name)
    {
        return \Yii::$app->params['hail812/yii2-adminlte3']['pluginMap'][$name] ?? self::$pluginMap[$name] ?? null;
    }
}