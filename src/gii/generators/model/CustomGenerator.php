<?php

namespace weebz\yii2basics\gii\generators\model;

class CustomGenerator extends Generator
{
    public function getName()
    {
        return 'Model Generator (Croac)';
    }

    public function getDescription()
    {
        return 'Gera modelos estendidos de ModelCommon com suporte a verGroup, enums, Yii::t etc.';
    }

    public function requiredTemplates()
    {
        return ['@vendor/weebz/yii2-basics/src/gii/generators/model/custom/model.php'];
    }

}
