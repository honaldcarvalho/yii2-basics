<?php

namespace weebz\yii2basics\widgets;

use Yii;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * @author Honald Carvalho da Silva <honalcarvalho@gmail.com>
 */
class Alert extends \yii\bootstrap5\Widget
{
    /**
     * @var array the options for rendering the close button tag.
     * Array will be passed to [[\yii\bootstrap\Alert::closeButton]].
     */
    public $model_root;
    public $model_child;
    public $model_root_id;
    public $model_child_id;


    /**
     * {@inheritdoc}
     */
    public function run()
    {

    }
}
