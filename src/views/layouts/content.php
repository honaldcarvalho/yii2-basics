<?php
/* @var $content string */

use weebz\yii2basics\models\Menu;
use yii\bootstrap5\Breadcrumbs;
use app\widgets\Alert;

?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">

            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?php
                        $controller_parts = explode('/',$this->context->id);
                        $controller = end($controller_parts);

                        $menu = Menu::find()->where(['LIKE','url',$controller])->one();

                        if (!is_null($menu)) {
                            echo "<i class='{$menu->icon} mr-2'></i> ";
                        }
                        if (!is_null($this->title)) {
                            echo \yii\helpers\Html::encode($this->title);
                        } else {
                            echo \yii\helpers\Inflector::camelize($this->context->id);
                        }
                        ?>
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <?php
                    echo Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        'options' => [
                            'class' => 'breadcrumb float-sm-right'
                        ]
                    ]);
                    ?>
                </div><!-- /.col -->
            </div><!-- /.row -->
            
            <?= Alert::widget() ?>
            
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <?= $content ?><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>