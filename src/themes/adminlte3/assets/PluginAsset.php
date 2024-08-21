<?php
namespace weebz\yii2basics\themes\adminlte3\assets;

use yii\web\AssetBundle;

class PluginAsset extends AssetBundle
{
    public $sourcePath = '@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist/plugins';
    public $depends = [
        'weebz\yii2basics\themes\adminlte3\assets\BaseAsset'
    ];

    public static $pluginMap = [
        'tinymce' => [
            'css' => 'tinymce/content/default/content.min.css',
            'js' => 'tinymce/content/default/content.js',
            'js' => 'tinymce/tinymce.min.js',
        ],
        'jquery-ui' => [
            'css' => 'jquery-ui/jquery-ui.min.css',
            'js' => 'jquery-ui/jquery-ui.min.js',
        ],
        'icheck-bootstrap' => [
            'css' => ['icheck-bootstrap/icheck-bootstrap.css']
        ],
        'cropperjs' => [
            'css' => 'cropperjs/css/cropper.css',
            'js' => 'cropperjs/js/cropper.js',
        ],
        'chart.js' => [
            'css' => 'chart.js/Chart.min.css',
            'js' => 'chart.js/Chart.min.js',
            'js' => 'chart.js/Chart.bundle.min.js',
        ],
        'toastr' => [
            'toastr/toastr.min.js',
            'toastr/toastr.min.css'
        ],
        'multiselect' => [
            'css'=>'multiselect/multiselect.min.js'
        ],
        'select2' => [
            'css' => 'select2/css/select2.min.css',
            'js' => 'select2/js/select2.min.js',
        ],
        'fancybox' => [
            'css' => 'fancybox5/fancybox.css',
            'js' => 'fancybox5/fancybox.umd.js',
        ],
        'fancybox' => [
            'css' => 'fancybox5/fancybox.css',
            'js' => 'fancybox5/fancybox.umd.js',
        ],
        'fontawesome' => [
            'css' => 'fontawesome-free/css/all.min.css'
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
        return \Yii::$app->params['weebz/yii2-basic']['pluginMap'][$name] ?? self::$pluginMap[$name] ?? null;
    }
}