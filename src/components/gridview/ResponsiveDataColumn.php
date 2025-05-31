<?php

namespace weebz\yii2basics\components\gridview;

use yii\grid\DataColumn;

class ResponsiveDataColumn extends DataColumn
{
    public $responsive = true;

    public function init()
    {
        parent::init();

        if (!isset($this->contentOptions['data-title']) && $this->responsive) {
            $this->contentOptions['data-title'] = $this->label ?: ucfirst($this->attribute);
        }

        $this->contentOptions['class'] = ($this->contentOptions['class'] ?? '') . ' col-' . $this->attribute;
    }
}
