<?php

namespace weebz\yii2basics\components\gridview;

use yii\grid\DataColumn;

class ResponsiveDataColumn extends DataColumn
{
    public function init()
    {
        parent::init();

        if (!isset($this->contentOptions['data-title'])) {
            $this->contentOptions['data-title'] = $this->label ?: $this->attribute;
        }
    }
}