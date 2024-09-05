<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace weebz\yii2basics\components\gridview;

use Yii;
use yii\helpers\Html;
use weebz\yii2basics\controllers\AuthController;

class ActionColumn extends \yii\grid\ActionColumn
{
    public $template = '{view} {update} {delete} {clone}';
    public $verGroup = true;
    public $controller = null;
    public $path = null;
    public $model = null;
    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('clone', 'clone');
        $this->initDefaultButton('view', 'eye-open');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('yii', 'Você tem certeza que quer remover esse item?'),
            'data-method' => 'post',
        ]);
    }
    
    /**
     * Initializes the default button rendering callback for single button.
     * @param string $name Button name as it's written in template
     * @param string $iconName The part of Bootstrap glyphicon class that makes it unique
     * @param array $additionalOptions Array of additional options
     * @since 2.0.11
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {

        if($this->controller === null) {
            $controller_parts = explode('\\',get_class(Yii::$app->controller));
            if($this->path === null){
                if(count($controller_parts) == 4)
                    $this->path = "{$controller_parts[0]}/{$controller_parts[2]}";
                else
                    $this->path = "{$controller_parts[0]}";
            }
            $controller_parts = explode('Controller',end($controller_parts));
            $this->controller = strtolower($controller_parts[0]);
            if(($tranformed = AuthController::addSlashUpperLower($controller_parts[0])) != false){
                $this->controller = $tranformed;
            }

        }

        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {

                $this->model = $model;
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                        break;
                    case 'clone':
                        $title = Yii::t('yii', 'Clone');
                        $icon = Html::tag('i', '', ['class' => "fas fa-clone"]);
                        break;
                    default:
                        $title = ucfirst($name);
                }
                
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                    'class'=>'btn btn-outline-secondary',
                ], $additionalOptions, $this->buttonOptions);

                $icon = isset($this->icons[$iconName])
                    ? $this->icons[$iconName]
                    : $icon;
                if($this->verGroup && !AuthController::isAdmin()){
                    return ( AuthController::verAuthorization($this->controller,$name,$model,$this->path)) ? Html::a($icon, $url, $options) : '';
                }else if(!AuthController::isAdmin()){
                    return (
                        AuthController::verAuthorization($this->controller,$name,null,$this->path)
                        ) ? Html::a($icon, $url, $options) : '';
                }else{
                    return Html::a($icon, $url, $options);
                }
            };
        }
    }
}