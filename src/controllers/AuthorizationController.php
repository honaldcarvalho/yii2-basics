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

class AuthorizationController extends ControllerCommon
{
    const ADMIN_GROUP_ID = 2;
    public $free = ['login', 'signup', 'error'];

    public static function isGuest()
    {
        return Yii::$app->user->isGuest;
    }

    public static function User(): ?User
    {
        return Yii::$app->user->identity;
    }

    public static function getUserGroups()
    {
        return self::isGuest()
            ? self::getUserByToken()?->getUserGroupsId()
            : self::User()?->getUserGroupsId();
    }

    public static function isAdmin(): bool
    {
        return !self::isGuest() && UserGroup::find()
            ->where(['user_id' => Yii::$app->user->id, 'group_id' => self::ADMIN_GROUP_ID])
            ->exists();
    }

    public static function getUserByToken()
    {
        $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
        if ($authHeader && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return User::find()
                ->where(['status' => User::STATUS_ACTIVE])
                ->andWhere(['or', ['access_token' => $matches[1]], ['auth_key' => $matches[1]]])
                ->one();
        }
        return null;
    }

    public function behaviors()
    {
        $request = Yii::$app->request;
        $action = $this->action->id;
        $controller = $this;

        $allow = in_array($action, $this->free) || self::isAdmin() || $this->pageAuth();

        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => fn() => date('Y-m-d H:i:s'),
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => $this->free, 'roles' => ['?']],
                    ['allow' => $allow, 'actions' => [$action], 'roles' => ['@']],
                ],
            ],
        ];

        if ($this->params->logging && Yii::$app->user->identity && $controller->id != 'log') {
            $log = new Log([
                'action' => $action,
                'ip' => $this->getUserIP(),
                'device' => $this->getOS(),
                'controller' => $this->id,
                'user_id' => Yii::$app->user->id,
            ]);

            if ($request->get('id')) {
                $log->data = $request->get('id');
            } elseif (!empty($request->post())) {
                $data = json_encode($request->post());
                if (!str_contains($data, 'password')) {
                    $log->data = $data;
                }
            }

            $log->save();
        }

        return $behaviors;
    }

    public function pageAuth(): bool
    {
        if (self::isGuest()) return false;
        if (self::isAdmin()) return true;

        $controllerId = Yii::$app->controller->id;
        $actionId = Yii::$app->controller->action->id;
        $path = self::getPath();

        $roles = Role::find()
            ->where(['path' => $path, 'controller' => $controllerId, 'status' => 1])
            ->andWhere(['in', 'group_id', self::User()->getUserGroupsId()])
            ->all();

        foreach ($roles as $role) {
            $actions = explode(';', $role->actions);
            if (in_array($actionId, $actions, true)) {
                return true;
            }
        }

        return false;
    }

    public static function verAuthorization($request_controller, $request_action, $model = null, $app_path = 'app')
    {
        if (self::isGuest()) return false;
        if (self::isAdmin()) return true;

        if (self::verifyLicense() === null) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'License expired!'));
            return false;
        }

        $groups = self::User()->getUserGroupsId();

        if ($model && $model->verGroup) {
            if ($request_action === 'view' && $model->group_id == 1) {
                return true;
            }
            if (!in_array($model->group_id, $groups)) {
                return false;
            }
        }

        $roles = Role::find()
            ->where([
                'path' => $app_path,
                'controller' => $request_controller,
                'status' => 1,
            ])
            ->andWhere(['in', 'group_id', $groups])
            ->all();

        foreach ($roles as $role) {
            $actions = explode(';', $role->actions);
            if (in_array($request_action, $actions)) {
                return true;
            }
        }

        return false;
    }

    public static function verifyLicense()
    {
        $groups = self::User()?->getUserGroupsId();
        if (self::isAdmin()) return true;

        $licenses = License::find()->where(['in', 'group_id' => $groups])->all();

        foreach ($licenses as $license) {
            if (strtotime($license->validate) >= strtotime(date('Y-m-d')) && $license->status) {
                return $license;
            }
        }

        return null;
    }

    protected function findModel($id, $model_name = null)
    {
        if (!$model_name) {
            $modelClass = str_replace(['controllers', 'Controller'], ['models', ''], static::getClassPath());
        } else {
            $modelClass = $model_name;
        }

        $model = $modelClass::find()->where(['id' => $id]);

        $modelObj = new $modelClass;
        if (
            property_exists($modelObj, 'verGroup') &&
            $modelObj->verGroup &&
            !self::isAdmin()
        ) {
            $groups = self::User()->getUserGroupsId();
            if (Yii::$app->controller->action->id === 'view') {
                $groups[] = 1;
            }

            $model->andFilterWhere(['in', 'group_id', $groups]);
        }

        if (($instance = $model->one()) !== null) {
            return $instance;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
