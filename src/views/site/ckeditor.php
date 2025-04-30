<?php

use weebz\yii2basics\widgets\Ckeditor;
use yii\helpers\Html;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                        <?= Ckeditor::widget([
                        'name' => 'editorContent',
                        'value' => '<p>Texto inicial...</p>',
                        'options' => ['id' => 'editor-custom', 'rows' => 10],
                        'clientOptions' => [
                            'toolbar' => [
                                'heading', '|',
                                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                                'loremIpsumSmall', 'loremIpsumBig'
                            ],
                        ],
                    ]);?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>
