<?php

namespace weebz\yii2basics\controllers;

use weebz\yii2basics\models\License;
use weebz\yii2basics\models\Log;
use weebz\yii2basics\models\Role;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class AuthController extends ControllerCommon
{
    const ADMIN_GROUP_ID = 2;
    public $free = ['login', 'signup', 'error'];

    static function isGuest()
    {
        return Yii::$app->user->isGuest;
    }

    static function User(): User|null
    {
        return Yii::$app->user->identity;
    }

    public static function getUserGroups()
    {
        if (self::isGuest()) {
            return self::getUserByToken()->getUserGroupsId();
        } else {
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
            if (!self::isGuest())
                return Yii::$app->session->get('group')->id;
        }

        $user = self::getUserByToken();
        if ($user)
            $user_groups = $user->getUserGroupsId();

        return end($user_groups);
    }

    public static function inGroups($grupos)
    {
        return UserGroup::find()
            ->select('user_id')
            ->where(['in', 'group_id', $grupos])
            ->andWhere(['usuario_id' => Yii::$app->user->identity->id])
            ->exists();
    }

    public static function isAdmin(): bool
    {
        return !self::isGuest() && UserGroup::find()
            ->where([
                'user_id' => Yii::$app->user->id,
                'group_id' => self::ADMIN_GROUP_ID,
            ])
            ->exists();
    }

    static function getUserByToken()
    {
        $user = null;
        $token = Yii::$app->request->headers["authorization"];
        if ($token !== null) {
            [$type, $value] = explode(' ', $token);
            if ($type == 'Bearer') {
                $user = User::find()->where(['status' => User::STATUS_ACTIVE])
                    ->filterwhere(['or', ['access_token' => $value], ['auth_key' => $value]])
                    ->one();
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
        if (in_array($action, $this->free) || self::isAdmin()) {
            $show = true;
        }

        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => fn() => date('Y-m-d H:i:s'),
            ],
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
                        'actions' => [$action],
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
                $log->user_id = Yii::$app->user->identity->id;

                if ($request->get('id')) {
                    $log->data = $request->get('id');
                } elseif ($request->post()) {
                    $data_json = json_encode($request->post());
                    if (!str_contains($data_json, 'password')) {
                        $log->data = $data_json;
                    }
                }

                $log->save();
            }
        }

        return $behaviors;
    }

    static function getLicense($model)
    {
        $licenses = $model->group->getLicenses()->all();
        foreach ($licenses as $license) {
            if (strtotime($license->validate) >= strtotime(date('Y-m-d')) && $license->status) {
                return $license;
            }
        }
        return null;
    }

    static function verifyLicense()
    {
        $user_groups = self::User()::userGroups()->all();
        if (self::isAdmin()) {
            return true;
        }

        $licenses = License::find()->andWhere(['in', 'group_id', $user_groups])->all();
        foreach ($licenses as $license) {
            if (strtotime($license->validate) >= strtotime(date('Y-m-d')) && $license->status) {
                return $license;
            }
        }
        return null;
    }

    public function pageAuth()
    {
        $show = false;

        if (!self::isGuest()) {
            $request_controller = Yii::$app->controller::class;
            $request_action = Yii::$app->controller->action->id;
            $groups = self::User()->getUserGroupsId();

            $query = Role::find()
                ->where([
                    'controller' => $request_controller,
                    'status' => 1,
                ])
                ->andWhere(['or', ['in', 'group_id', $groups], ['group_id' => self::User()->group_id]])
                ->all();

            foreach ($query as $role) {
                $actions = explode(';', $role->actions ?? '');
                foreach ($actions as $action) {
                    if (trim($action) === trim($request_action)) {
                        $show = true;
                        break 2;
                    }
                }
            }
        }

        return $show;
    }

    public static function verAuthorization($request_controller, $request_action, $model = null, $origin = '*')
    {
        if (!self::isGuest()) {
            if (self::isAdmin()) {
                return true;
            }

            if (self::verifyLicense() === null) {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'License expired!'));
                return [];
            }

            $groups = self::User()->getUserGroupsId();

            if ($model && $model->verGroup) {
                if ($request_action == 'view' && $model->group_id == 1) {
                    return true;
                }
                if (!in_array($model->group_id, $groups)) {
                    return false;
                }
            }

            return Role::find()
                ->where([
                    'controller' => $request_controller,
                    'action' => $request_action,
                    'status' => 1,
                ])
                ->andWhere(['in', 'group_id', $groups])
                ->exists();
        }

        return false;
    }

    protected function findModel($id, $model_name = null)
    {
        if (!$model_name) {
            $path = str_replace('controllers', 'models', static::getClassPath());
            $path_model = str_replace('Controller', '', $path);
            $model_obj = new $path_model;
            $model = $path_model::find()->where([$model_obj->tableSchema->primaryKey[0] => $id]);
        } else {
            $model_obj = new $model_name;
            $model = $model_name::find()->where([$model_obj->tableSchema->primaryKey[0] => $id]);
        }

        if (property_exists($model_obj, 'verGroup') && $model_obj->verGroup && !self::isAdmin()) {
            $groups = self::User()->getUserGroupsId();
            if (Yii::$app->controller->action->id == 'view') {
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
