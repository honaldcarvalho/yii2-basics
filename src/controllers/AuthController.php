<?php

namespace weebz\yii2basics\controllers;

use weebz\yii2basics\models\License;
use weebz\yii2basics\models\Log;
use Yii;

use weebz\yii2basics\models\Rule;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Common controller
 */
class AuthController extends ControllerCommon {

    public $free = ['login', 'signup','error'];

    
    static function isGuest()
    {
        return Yii::$app->user->isGuest;
    }
    
    /**
     * Cast \yii\web\IdentityInterface to \weebz\yii2basics\models\User
     */
    static function User(): User|null
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

    static function userGroups()
    {
        $groups = [];
        $user_groups = [];

        if (Yii::$app->user->identity != null) {
            $user_groups = self::User()::userGroups()->all();
        }
        foreach ($user_groups as $user_group) {
            $groups[] = $user_group->group_id;
        }
        return $groups;
    }

    static function userGroup()
    {
        $user_groups = [];

        $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
        if (!$authHeader || !preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            if(!self::isGuest())
                return Yii::$app->session->get('group')->id;
        }

        $user = self::getUserByToken();
        if($user)
            $user_groups = $user->getUserGroupsId();

        return end($user_groups);
    }

    public static function inGroups($grupos)
    {
        if (UserGroup::find()->select('user_id')->where(['in', 'group_id', $grupos])->andWhere(['usuario_id' => Yii::$app->user->identity->id])->count() > 0) {
            return true;
        }
        return false;
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
        if($token !== null) {
            [$type,$value] = explode(' ',$token);
            if($type == 'Bearer'){
                $user = User::find()->where(['status'=>User::STATUS_ACTIVE])->filterwhere(['or',['access_token'=>$value],['auth_key'=>$value]])->one();
            }
        }
        return $user;
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $request = Yii::$app->request;
  
        $controller = $this;
        $action = $this->action->id;

        $show = $this->pageAuth();
        if(in_array($action,$this->free)){
            $show = true;
        }

        $behaviors = [
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

        if ($this->params->logging && $controller->id != 'log') {
            if (Yii::$app->user->identity !== null) {
                $log = new Log();
                $log->action = $action;
                $log->ip = $this->getUserIP();
                $log->device = $this->getOS();
                $log->controller = Yii::$app->controller->id;
                $log->user_id = Yii::$app->user->identity !== null ? Yii::$app->user->identity->id : 0;

                if ($request->get() !== null && isset($request->get()['id'])) {
                    $log->data = $request->get()['id'];
                }
                if ($request->post() !== null && !empty($request->post())) {
                    $data_json = json_encode($request->post());
                    if (!str_contains($data_json, 'password'))
                        $log->data = $data_json;
                }

                $log->save();
            }
        }

        return $behaviors;
    }

    static function getLicense($model)
    {
        $license_valid = null;
        $licenses = $model->group->getLicenses()->all();
        foreach ($licenses as $key => $license) {
            if (strtotime($license->validate) >= strtotime(date('Y-m-d')) && $license->status) {
                $license_valid = $license;
            }
        }
        return $license_valid;
    }

    static function verifyLicense()
    {
        $user_groups = self::User()::userGroups()->all();
        $license_valid = null;
        if (self::isAdmin()) {
            return true;
        }

        $licenses = License::find()->andWhere(['in', 'group_id', $user_groups])->all();
        //se não tiver licensa libera
        foreach ($licenses as $key => $license) {
            if (strtotime($license->validate) >= strtotime(date('Y-m-d')) && $license->status) {
                $license_valid = $license;
            }
        }
        return $license_valid;
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

            if (self::verifyLicense() === null) {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'License expired!'));
                return [];
            }
            
            $groups = self::User()->getUserGroupsId();

            $query_rules = Rule::find()->where(['path' => $app_path, 'controller' => $request_controller, 'status' => 1]);

            if (!self::isAdmin()) {
                if($model && $model->verGroup){
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
