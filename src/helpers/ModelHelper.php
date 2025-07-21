<?php

namespace weebz\yii2basics\helpers;

class ModelHelper
{
    public $namespaces =  [];

    public function __construct() {
        $this->namespaces =  [
                'app\models' => \Yii::getAlias('@app/models'),
                'weebz\yii2basics\models' => \Yii::getAlias('@weebz/yii2basics/models'),
        ];
    }

    public static function getAllModelClasses(): array
    {
        foreach (self::$namespaces as $ns => $path) {
            if (!is_dir($path)) continue;
            $files = scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^[A-Z]\w+\.php$/', $file)) {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $fqcn = $ns . '\\' . $className;

                    if (class_exists($fqcn) && is_subclass_of($fqcn, \yii\db\ActiveRecord::class)) {
                        $models[$fqcn] = $className;
                    }
                }
            }
        }

        return $models;
    }
}
