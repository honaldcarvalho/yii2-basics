<?php

namespace weebz\yii2basics\widgets;

use Yii;
use weebz\yii2basics\controllers\ AuthController;
use yii\base\Widget;
use yii\helpers\Html;

class DefaultButtons extends Widget
{
    public $controller = null;
    public $path = null;
    public $model = null;
    public $buttons = '';
    public $extras = [];
    public $verGroup = true;
    public $visible = true;
    
    
    public $buttons_name = [
        'index'=>  'List',
        'create'=>  'Create',
        'update'=>  'Update',
        'delete'=>  'Delete',
        'clone' => 'Clone'
    ];
    
    public $show = ['index','create', 'update','delete','clone'];

    public function init()
    {
        parent::init();

        $controller_parts = explode('\\',get_class(Yii::$app->controller));

        if(count($controller_parts) == 4)
            $this->path = "{$controller_parts[0]}/{$controller_parts[2]}";
        else
            $this->path = "{$controller_parts[0]}";

        $controller_parts = explode('Controller',end($controller_parts));
        $this->controller = strtolower($controller_parts[0]);

        if(($tranformed =  AuthController::addSlashUpperLower($controller_parts[0])) != false){
            $this->controller = $tranformed;
        }
        
        $show = true;
        if($this->verGroup){
            if($this->model != null && !in_array($this->model->group_id, AuthController::getUserGroups())){
                $show = false;
            }
        }

        $this->buttons .= '<div class="btn-group">';

        if(in_array('index',$this->show) &&  AuthController::verAuthorization($this->controller,'index',null,$this->path) && $show){
            $this->buttons .= Html::a(
                    '<i class="fas fa-list-ol"></i>&nbsp;' . Yii::t('app', $this->buttons_name['index'] ?? 'index'),
                    ['index'], 
                    ['class' => 'btn btn-primary']);
        }
        
        if(in_array('create',$this->show) &&  AuthController::verAuthorization($this->controller,'create',null,$this->path) && $show){
            $this->buttons .= Html::a( 
                    '<i class="fas fa-plus-square"></i>&nbsp;' . Yii::t('app', $this->buttons_name['create'] ?? 'create'), 
                    ['create'], 
                    ['class' => 'btn btn-success']);                       
        }

        if(in_array('update',$this->show) &&  AuthController::verAuthorization($this->controller,'update',$this->model,$this->path) && $this->model && $show){
            if(!is_array($this->model->primaryKey)){
                $link = ['update', 'id' =>  $this->model->id];
            }else{
                $link = array_merge(['update'],$this->model->primaryKey);
            }
            $this->buttons .= Html::a(
                    '<i class="fas fa-edit"></i>&nbsp;' . Yii::t('app', $this->buttons_name['update'] ?? 'update'), 
                    $link, 
                    ['class' => 'btn btn-warning']);
        }
        
        if(in_array('delete',$this->show) &&  AuthController::verAuthorization($this->controller,'delete',$this->model,$this->path) && $this->model && $show){
            if(!is_array($this->model->primaryKey)){
                $link = ['delete', 'id' =>  $this->model->id];
            }else{
                $link = array_merge(['delete'],$this->model->primaryKey);
            }
            $this->buttons .= Html::a('<i class="fas fa-trash"></i>&nbsp;'. Yii::t('app', $this->buttons_name['delete'] ?? 'delete'), 
                    $link, 
                    [
                        'class' => 'btn btn-danger input-group-text',
                        'data' => [
                            'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                            'method' => 'post'
                        ],
                     ]);
    
        }

        if(in_array('clone',$this->show) &&  AuthController::verAuthorization($this->controller,'clone',$this->model,$this->path) && $show){
            $this->buttons .= Html::a( 
                '<i class="fas fa-clone"></i>&nbsp;' . Yii::t('app', $this->buttons_name['clone'] ?? 'clone'), 
                ['clone', 'id' =>  $this->model->id], 
                ['class' => 'btn btn-dark']);                       
        }

        foreach($this->extras as $extra){
            $visible = true;
            if(isset($extra['visible'])){
                $visible = $extra['visible'];
            }
            if( AuthController::verAuthorization($extra['controller'],$extra['action'],$this->model,$this->path) && $visible){
                $this->buttons .= Html::a(
                        $extra['icon'] . Yii::t('app', $extra['text']),
                        $extra['link'], 
                        $extra['options']);
            }
        }
        $this->buttons .= "</div>";
    }

    public function run()
    {
        return $this->buttons;
    }
}