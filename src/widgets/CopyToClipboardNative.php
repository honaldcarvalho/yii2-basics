<?php
/**
 *
 * @author Honald Carvalho <honaldcarvalho@gmail.com>
 * 
 * Example: CopyToClipboardNative::widget(['element_id'=>'#code','attr'=>'val','text'=>'Copy','message'=>'Copy to clipboard'])
 */

namespace weebz\yii2basics\widgets;

use Yii;
use yii\helpers\Html;
use yii\web\View;

class CopyToClipboardNative extends \yii\bootstrap5\Widget
{
    public $element_id = null;
    public $attr = 'val';
    public $text    = 'Copy';
    public $message = 'Copy to clipboard';
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->text = Yii::t('app',$this->text);
        $message = Yii::t('app',$this->message);
        parent::init();
        $script = <<< JS

            $('.ccnw').click(function(e) {
                
                let element_id = $(this).data('element_id');
                var copyText = document.getElementById(element_id);

                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);

                if(toastr !== undefined){
                    toastr.success('$message');
                }else{
                    alert('$message');
                }
            });
        JS;

        \Yii::$app->view->registerJs($script, View::POS_END);
    }

    public function run()
    {
        return Html::a(
            "<i class='fas fa-copy'></i> {$this->text}",
            "javascript:;",
            [
                'class'=>'btn btn-default ccnw',
                'data-attr'=>"{$this->attr}",
                'data-element_id'=>"{$this->element_id}" 
            ]
        );
    }
}
