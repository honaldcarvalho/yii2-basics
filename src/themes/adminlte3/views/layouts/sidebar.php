<?php
if(Yii::getAlias('@leftbar', false)) {
    echo $this->render('leftbar');
} else {
    echo $this->render('@vendor/weebz/yii2-basics/src/views/menu/sidebar');
}