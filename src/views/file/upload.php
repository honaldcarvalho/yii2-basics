<?php

use app\widgets\UploadFile;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>

<?php $form = ActiveForm::begin(); ?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-md-12">
                    <div class="row">

                        <?=
                        app\widgets\FileInput::widget(
                            [
                                'field_name' => 'logo',
                                'file_name' => '',
                                'label' => 'Profile',
                                'folder_id' => 3,
                                'action' => 'file/send',
                                'as_blob' => 1,
                                'aspectRatio' => '1/1',
                                'extensions' => ['jpeg', 'jpg', 'png']
                            ]
                        );
                        ?>


                        <div class="col-md-12">
                            <?= Html::submitButton('Salvar', ['class' => 'btn btn-success']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>


                    </div>
                </div>
            </div>
            <!--.card-body-->
        </div>
        <!--.card-->
    </div>
</div>