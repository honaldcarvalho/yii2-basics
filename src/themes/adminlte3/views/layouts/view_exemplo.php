<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel weebz\yii2basics\models\RegraSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'View';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                   <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a('<i class="fas fa-chevron-circle-left"></i> Voltar', ['view','id' => $model->id], ['class' => 'btn btn-info']) ?>
                            <?= Html::a('<i class="fas fa-plus-square"></i> Novo', ['create'], ['class' => 'btn btn-success']) ?>
                            <?= Html::a('<i class="fas fa-list-ol"></i> Lista', ['index'], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                            <?= Html::a('<i class="fas fa-trash"></i> Remover', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Você tem certeza que quer remover este item??',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </div>
                    </div>

                    ---
                    
                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
