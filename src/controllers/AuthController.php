<?php

namespace weebz\yii2basics\controllers;

use Yii;
use yii\web\Controller;
use weebz\yii2basics\models\Rule;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Common controller
 */
class AuthController extends Controller {

    public $free = ['login', 'signup','error'];

    public static function getClassPath()
    {
        return get_called_class();
    }

    public static function getPath()
    {
        $path_parts = explode("\\", self::getClassPath());
        if (count($path_parts) == 4)
            return "{$path_parts[0]}/{$path_parts[2]}";

        return strtoupper($path_parts[0]);
    }

    static function addSlashUpperLower($string)
    {

        $split = str_split($string);
        $count = 0;
        $cut = 0;

        foreach ($split as $key => $value) {
            if (ctype_upper($value) && $count > 0) {
                $cut = $key;
            }
            $count++;
        }

        $first = strtolower(substr($string, 0, $cut));
        $second = strtolower(substr($string, $cut));

        if (!empty($first))
            return "{$first}-{$second}";

        return false;
    }
    
    static function isGuest()
    {
        return Yii::$app->user->isGuest;
    }
    
    /**
     * Cast \yii\web\IdentityInterface to \weebz\yii2basics\models\User
     */
    static function User(): User
    {
        return Yii::$app->user->identity;
    }

    /** 
        return id of user group's
    */
    public static function getUserGroups()
    {
        if(self::isGuest()){
           return self::getUserByToken()->getUserGroupsId();
        }else {
           return self::User()->getUserGroupsId();
        }
    }

    public static function isAdmin()
    {
        if (!Yii::$app->user->isGuest && UserGroup::find()->where(['user_id' => Yii::$app->user->identity->id, 'group_id' => [2]])->one()) {
            return true;
        }
        return false;
    }

    static function getUserByToken() {
        $user = null;
        $token = Yii::$app->request->headers["authorization"];
        [$type,$value] = explode(' ',$token);
        if($type == 'Bearer'){
            $user = User::find()->where(['status'=>User::STATUS_ACTIVE])->filterwhere(['or',['access_token'=>$value],['auth_key'=>$value]])->one();
        }
        return $user;
    }
    
    public function behaviors()
    {
        $request = Yii::$app->request;
        $controller = Yii::$app->controller->id;
        $action = Yii::$app->controller->action->id;

        $show = $this->pageAuth();
        if(in_array($action,$this->free)){
            $show = true;
        }
        
        
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => $this->free,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => $show,
                        'actions' => ["{$action}"],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    
    public function pageAuth()
    {
        $show = false;
        if(!self::isGuest()){

            $app_path = self::getPath();
            $request_controller = Yii::$app->controller->id;
            $request_action = Yii::$app->controller->action->id;
            $groups = self::User()->getUserGroupsId();
            $query_rules = Rule::find()->where(['path' => $app_path, 'controller' => $request_controller, 'status' => 1]);

            if (!self::isAdmin()) {
                $rules = $query_rules->andWhere(['or', ['in', 'group_id', $groups],['group_id'=> self::User()->group_id]])->all();
            } else {
                $rules = $query_rules->all();
            }

            foreach ($rules as $key => $rule) {

                $actions = explode(';',$rule->actions);

                foreach($actions as $action) {
                    if(trim($action) == trim($request_action)){
                        $show = true;
                        break;
                    }
                }                
            }
        }

        return $show;

    }

    public static function verAuthorization($request_controller, $request_action, $model = null, $app_path = 'app')
    {
        if(!self::isGuest()){

            $groups = self::User()->getUserGroupsId();

            $query_rules = Rule::find()->where(['path' => $app_path, 'controller' => $request_controller, 'status' => 1]);

            if (!self::isAdmin()) {
                if($model){
                    //group 1: common
                    if($request_action == 'view' && $model->group_id == 1){
                        return true;
                    }
                    if(!in_array($model->group_id, $groups)){
                        return false;
                    }
                }
                $rules = $query_rules->andWhere(['or', ['in', 'group_id', $groups],['group_id'=> self::User()->group_id]])->all();
            } else {
                $rules = $query_rules->all();
            }

            foreach ($rules as $key => $rule) {
                $actions = explode(';',$rule->actions);
                foreach($actions as $action) {

                    if($action == $request_action){
                        return true;
                    }
                }                
            }

        }
        return false;
    }

    /**
     * Finds the Captive model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Model the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

     protected function findModel($id)
     {
 
         //$path = str_replace('backend','common'); --> ADVANCED
         $path = str_replace('controllers', 'models', static::getClassPath());
         $path_model = str_replace('Controller', '', $path);
         $model_obj = new $path_model;
 
         $model = $path_model::find()->where([$model_obj->tableSchema->primaryKey[0] => $id]);
 
         if ($model_obj->verGroup !== null && $model_obj->verGroup && !self::isAdmin()) {
            $groups = self::User()->getUserGroupsId();
            if(Yii::$app->controller->action->id == 'view') {
                $groups[] = 1;
            }
            $model->andFilterWhere(['in', 'group_id', $groups]);
         }
 
         $model = $model->one();
 
         if ($model !== null) {
             return $model;
         }
 
         throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
     }

}
