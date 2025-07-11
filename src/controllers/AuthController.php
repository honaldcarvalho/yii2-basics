<?php

namespace weebz\yii2basics\controllers;

use weebz\yii2basics\models\License;
use weebz\yii2basics\models\Log;
use Yii;

use weebz\yii2basics\models\Rule;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use yii\behaviors\TimestampBehavior;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Common controller
 */
class AuthController extends ControllerCommon
{

    const ADMIN_GROUP_ID = 2;
    public $free = ['login', 'signup', 'error'];

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
        if (UserGroup::find()->select('user_id')->where(['in', 'group_id', $grupos])->andWhere(['usuario_id' => Yii::$app->user->identity->id])->count() > 0) {
            return true;
        }
        return false;
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
                $user = User::find()->where(['status' => User::STATUS_ACTIVE])->filterwhere(['or', ['access_token' => $value], ['auth_key' => $value]])->one();
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
                'value' => function () {
                    return date('Y-m-d H:i:s'); // Respeita o timeZone do Yii2
                },
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
        if (!self::isGuest()) {

            $app_path = self::getPath();
            $request_controller = Yii::$app->controller->id;
            $request_action = Yii::$app->controller->action->id;
            $groups = self::User()->getUserGroupsId();
            $query_rules = Rule::find()->where(['path' => $app_path, 'controller' => $request_controller, 'status' => 1]);

            if (!self::isAdmin()) {
                $rules = $query_rules->andWhere(['or', ['in', 'group_id', $groups], ['group_id' => self::User()->group_id]])->all();
            } else {
                $rules = $query_rules->all();
            }

            foreach ($rules as $key => $rule) {

                $actions = explode(';', $rule->actions);

                foreach ($actions as $action) {
                    if (trim($action) == trim($request_action)) {
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
        // Verifica se o usuário está autenticado
        if (!self::isGuest()) {

            // Se for administrador, sempre permite o acesso
            if (self::isAdmin()) {
                return true;
            }

            // Verifica se a licença está válida
            if (self::verifyLicense() === null) {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'License expired!'));
                return [];
            }

            // Obtém todos os IDs dos grupos aos quais o usuário pertence
            $groups = self::User()->getUserGroupsId();

            // Cria a consulta base das regras de acesso para o controller solicitado
            $query_rules = Rule::find()->where([
                'path' => $app_path,
                'controller' => $request_controller,
                'status' => 1
            ]);

            // Se foi passado um modelo e ele possui controle por grupo
            if ($model && $model->verGroup) {

                // Permite visualização de modelos com group_id = 1 (grupo comum)
                if ($request_action == 'view' && $model->group_id == 1) {
                    return true;
                }

                // Se o grupo do modelo não está entre os grupos do usuário, nega acesso
                if (!in_array($model->group_id, $groups)) {
                    return false;
                }
            }

            // Filtra regras que pertencem a um dos grupos do usuário
            $rules = $query_rules
                ->andWhere([
                    'or',
                    ['in', 'group_id', $groups],
                    ['group_id' => self::User()->group_id]
                ])
                ->all();

            // Percorre todas as regras encontradas
            foreach ($rules as $rule) {
                $actions = explode(';', $rule->actions);
                if (in_array($request_action, $actions)) {
                    return true;
                }
            }
        }

        // Caso nenhuma verificação permita acesso, retorna false
        return false;
    }

    /**
     * Finds the Captive model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Model the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

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

        // Verifica se o modelo tem a propriedade verGroup E se o usuário NÃO é admin
        if (
            property_exists($model_obj, 'verGroup') &&
            $model_obj->verGroup &&
            !self::isAdmin()
        ) {
            $groups = self::User()->getUserGroupsId();

            // Permite visualizar registros do grupo comum (id=1) na action "view"
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
