<?php

namespace weebz\yii2basics;

use yii\base\Module;

/**
 * Módulo para uso exclusivo no console, evitando conflitos com controllers web/API.
 */
class ModuleConsole extends Module
{
    /**
     * Namespace padrão para comandos deste módulo no console
     * (ex: php yii basics/hello)
     */
    public $controllerNamespace = 'app\commands';

    public function init()
    {
        parent::init();
        // Qualquer inicialização específica para console pode ir aqui
    }
}
