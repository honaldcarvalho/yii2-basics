<?php

namespace weebz\yii2basics\components\gridview;

use yii\grid\DataColumn;

class ResponsiveDataColumn extends DataColumn
{
    public function init()
    {
        parent::init();

        // Define o label como fallback se não houver definido manualmente
        if (!isset($this->contentOptions['data-title'])) {
            $label = $this->label ?: ucfirst($this->attribute);
            $this->contentOptions['data-title'] = $label;
        }
    }
}
