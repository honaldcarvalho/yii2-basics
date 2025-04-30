<?php
use yii\helpers\Html;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                        <?= Html::widget(\weebz\yii2basics\widgets\Ckeditor::class, [
                            'options' => ['rows' => 20]
                        ]); ?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>
