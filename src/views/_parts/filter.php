<?php

    $path = explode('\\',get_class($searchModel));
    $modelName = end($path);
    $collapsed = 'collapsed-card';
    $display = 'none';

    if(isset($_GET["{$modelName}"])){

        foreach ($_GET["{$modelName}"] as $parametro){
            if(!empty($parametro)){
                $collapsed = "";
                $display = 'block';
            }
        }
    }

    $script = <<< JS

        function clearFilters(){
            $('#w0').trigger("reset");
            $('input').attr('value','');
            $('select').val('').trigger('change');
            //$('select').empty().trigger("change");
        }

        $(function(){
            $('.btn-reset').on('click',function(){
                clearFilters();
            });
        });
    JS;

    $this::registerJs($script);

?>
<div class="row">
    <div class="col-md-12">

        <div class="card <?= $collapsed ?>">
            <div class="btn card-header" data-card-widget="collapse" title="Collapse">
                <label class="card-title text-white"><i class="fa-solid fa-filter"></i> <?= Yii::t('app','Filters')?></label>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display:<?= $display ?>;">
                <div class="col-md-12">

                    <?= $this->render("{$view}/_search", ['model' => $searchModel]) ?>

                </div>
            </div>
        </div>
    </div>

</div>