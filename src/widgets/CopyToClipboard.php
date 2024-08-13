<?php
/**
 *
 * @author Honald Carvalho <honaldcarvalho@gmail.com>
 * 
 */

namespace weebz\yii2basics\widgets;

use Yii;
use yii\helpers\Html;
use yii\web\View;

class CopyToClipboard extends \yii\bootstrap5\Widget
{
    public $element = null;
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
            $('.copyClipboard').click(function(e) {
                var temp = $("<input>");
                $("body").append(temp);
                temp.val($($(this).data('element')).attr($(this).data('attr'))).select();
                document.execCommand("copy");
                temp.remove();
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
                'class'=>'btn btn-default copyClipboard',
                'data-attr'=>"{$this->attr}",
                'data-element'=>"{$this->element}" 
            ]
        );
    }
}
