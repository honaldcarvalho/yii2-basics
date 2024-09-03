<?php

namespace weebz\yii2basics\controllers;

use weebz\yii2basics\controllers\rest\AuthController;
use weebz\yii2basics\models\License;
use weebz\yii2basics\models\Log;
use weebz\yii2basics\models\Params;
use weebz\yii2basics\models\Rule;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use weebz\yii2basics\models\ModelCommon;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\symfonymailer\Mailer;
use yii\web\NotFoundHttpException;

/**
 * Description of Controller
 *
 * @author Honald Carvalho
 * 
 * 
 */

class ControllerCommon extends \yii\web\Controller
{

    public  $guest  = [];
    public  $free   = [];
    private $fixed  = [];
    public $access = [];
    public $params = null;
    static $assetsDir;

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

    public function behaviors()
    {

        $this->params = Params::findOne(1);
        $language = null;

        self::$assetsDir = Yii::$app->assetManager->getPublishedUrl('@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist');
        
        foreach ($this->params->attributes as $key => $param) {
            Yii::setAlias("@{$key}", "$param");
        }

        $cookies = Yii::$app->request->cookies;
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        $request = Yii::$app->request;

        if (!\Yii::$app->user->isGuest) {
            $language = \Yii::$app->user->identity->language->code;
        } else if (($cookie = $cookies->get('lang')) !== null && !isset($post['lang'])) {
            $language = $cookie->value;
        } else if (isset($post['lang'])) {

            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'lang',
                'value' => $post['lang'],
            ]));
        } else {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'lang',
                'value' => 'pt-BR',
            ]));
        }
        \Yii::$app->language = $language ?? $this->params->language->code;

        $this->fixed = ['login', 'logout', 'error', 'gcaptcha', 'about', 'contact', 'signup', 'request-password-reset', 'resend-verification-email'];
        $app_path = self::getPath();
        $this->access = $this->getAuthorizationActions(Yii::$app->controller,$get,$app_path);

        $this->access = array_merge($this->access, $this->fixed);
        $this->access = array_merge($this->access, $this->free);

        if (!Yii::$app->user->isGuest) {
            $this->access = array_merge($this->access, $this->guest);
        }

        if ($this->params->logging && Yii::$app->controller->id != 'log') {
            if (Yii::$app->user->identity !== null) {
                $log = new Log();
                $log->action = Yii::$app->controller->action->id;
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

        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => array_merge($this->access, $this->fixed),
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    // restrict access to
                    'Origin' => ['http://localhost:8080', 'http://localhost:8081'],
                ],

            ],
        ];
    }

    public static function error($th)
    {   
        if(isset($th->statusCode )){
            if($th->statusCode == 400){
                throw new \yii\web\BadRequestHttpException($th->getMessage());
            } else if($th->statusCode == 401){
                throw new \yii\web\MethodNotAllowedHttpException($th->getMessage());
            } else if($th->statusCode == 403){
                throw new \yii\web\ForbiddenHttpException($th->getMessage());
            } else if($th->statusCode == 404){
                throw new \yii\web\NotFoundHttpException($th->getMessage());
            }
        }
        throw new \yii\web\ServerErrorHttpException(Yii::t('app', $th->getMessage()));
    
    }
    /**
     * Cast \yii\web\IdentityInterface to \weebz\yii2basics\models\User
     */
    static function User(): User
    {
        return Yii::$app->user->identity;
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

    public static function customControllersUrl($controllers, $folder = 'custom')
    {
        $rules = [];
        foreach ($controllers as $key => $controller) {
            $rules["{$controller}/<id:\d+>"] = "{$folder}/{$controller}/view";
            $rules["{$controller}/<action>/<id:\d+>"] = "{$folder}/{$controller}/<action>";
            $rules["{$controller}/<action>"] = "{$folder}/{$controller}/<action>";
            $rules["{$controller}"] = "{$folder}/{$controller}";
        }
        return $rules;
    }

    static function isGuest()
    {
        return Yii::$app->user->isGuest;
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
        if (ControllerCommon::isAdmin()) {
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
        if(ControllerCommon::isGuest()){
            $user_groups = AuthController::getUserByToken()->getUserGroupsId();
            return end($user_groups);
        }else {
            return Yii::$app->session->get('group')->id;
        }

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

    /** 
        return id of user group's
    */
    public static function getUserGroups()
    {
        
        if(ControllerCommon::isGuest()){
           return AuthController::getUserByToken()->getUserGroupsId();
        }else {
           return self::User()->getUserGroupsId();
        }

    }

    public function getAccess($controller, $path = 'backend')
    {

        $actions = [];
        $groups = [1]; //  1 -> ALL
        $user_groups = [];
        $actions_user = null;
        $actions_groups = null;

        $remote_addr =  $_SERVER['REMOTE_ADDR'] ?? null;

        if (!Yii::$app->user->isGuest) {

            if (self::verifyLicense() === null) {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'License expired!'));
                return [];
            }

            $user_groups = self::User()::userGroups()->all();

            foreach ($user_groups as $user_group) {
                $groups[] = $user_group->group_id;
            }
            $actions_user = Rule::find()->where(['user_id' => Yii::$app->user->identity->id, 'path' => $path, 'controller' => $controller, 'status' => 1])->asArray()->one();
        }

        $actions_groups = Rule::find()->where(['controller' => $controller, 'path' => $path, 'status' => 1])->andWhere(['in', 'group_id', $groups])->asArray()->all();

        if ($actions_user === null && $actions_groups === null) {
            return [];
        } else if ($actions_user === null && $actions_groups !== null) {
            foreach ($actions_groups as  $actions_group) {
                $origins = explode(';', $actions_group['origin']);
                if (in_array($remote_addr, $origins) || in_array('*', $origins)) {
                    $actions = array_merge($actions, explode(';', $actions_group['actions']));
                }
            }
        } else if ($actions_user !== null && $actions_groups !== null) {

            foreach ($actions_groups as  $actions_group) {
                $origins = explode(';', $actions_group['origin']);
                if (in_array($remote_addr, $origins) || in_array('*', $origins)) {
                    $actions = array_merge($actions, explode(';', $actions_group['actions']));
                }
            }
            $origins = explode(';', $actions_user['origin']);
            if (in_array($remote_addr, $origins)) {
                $actions = array_merge($actions, explode(';', $actions_user['actions']));
            }
        }

        if (isset($actions) && !empty($actions))
            return $actions;

        return [];
    }

    public static function getPermission($controller, $action, $model, $path = 'backend')
    {
        $actions = [];
        $actions_group = [];
        $actions_user = [];
        $groups = [];

        if (!Yii::$app->user->isGuest) {
            if(ControllerCommon::isAdmin())
                return true;
            $remote_addr =  $_SERVER['REMOTE_ADDR'] ?? null;

            $groups = self::getUserGroups();

            $actions_user = Rule::find()->where(['user_id' => Yii::$app->user->identity->id, 'path' => $path, 'controller' => $controller, 'status' => 1])->asArray()->one();
            $actions_groups = Rule::find()->where(['path' => $path, 'controller' => $controller, 'status' => 1])->andWhere(['in', 'group_id', $groups])->asArray()->all();

            if ($actions_user === null && $actions_groups === null) {
                return [];
            } else if ($actions_user === null && $actions_groups !== null) {
                foreach ($actions_groups as  $actions_group) {
                    $origins = explode(';', $actions_group['origin']);
                    if (in_array($remote_addr, $origins) || in_array('*', $origins)) {
                        $actions = array_merge($actions, explode(';', $actions_group['actions']));
                    }
                }
            } else if ($actions_user !== null && $actions_groups !== null) {

                foreach ($actions_groups as  $actions_group) {
                    $origins = explode(';', $actions_group['origin']);
                    if (in_array($remote_addr, $origins) || in_array('*', $origins)) {
                        $actions = array_merge($actions, explode(';', $actions_group['actions']));
                    }
                }
                $origins = explode(';', $actions_user['origin']);
                if (in_array($remote_addr, $origins)) {
                    $actions = array_merge($actions, explode(';', $actions_user['actions']));
                }
            }

            if (isset($actions) && !empty($actions)) {
                if (in_array($action, $actions)) {
                    return true;
                }
            }
        }


        return false;
    }

    public static function getPermissions($controllers)
    {
        $groups = [];
        $user_groups = [];

        if (!Yii::$app->user->isGuest) {

            $user_groups = self::User()::userGroups()->all();
            foreach ($user_groups as $user_group) {
                $groups[] = $user_group->group_id;
            }
            $actions_user = Rule::find()->where(['user_id' => Yii::$app->user->identity->id, 'status' => 1])
                ->andWhere(['in', 'controller', explode(';', $controllers)])->asArray()->one();

            $actions_group = Rule::find()->where(['status' => 1])
                ->andWhere(['in', 'group_id', $groups])
                ->andWhere(['in', 'controller', explode(';', $controllers)])->asArray()->all();

            if ($actions_user !== null || $actions_group !== null) {
                return true;
            }
        }
        return false;
    }

    public static function getAuthorization($controller, $action, $model, $path = 'backend')
    {

        if (!Yii::$app->user->isGuest) {
            $actions = self::getAuthorizationActions($controller, $model, $path);
            if (isset($actions) && !empty($actions)) {
                if (in_array($action, $actions)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getAuthorizationActions($controller, $params = null, $app_path = 'app')
    {
        $actions = [];
        $groups = [];
        $model = null;
        $path = '';

        if (!Yii::$app->user->isGuest) {

            if (self::verifyLicense() === null) {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'License expired!'));
                return [];
            }
            
            $groups = self::getUserGroups();

            $remote_addr =  $_SERVER['REMOTE_ADDR'] ?? null;

            $model_name = str_replace('controllers','models',Yii::$app->controller::class);
            $model_name = str_replace('Controller','',$model_name);

            if(gettype($controller) != 'string'){
                $controller_parts = explode('/',$controller->id );
                $controller = end($controller_parts);
            }

            if($params != null && !empty($params) && isset($params['id']) && $controller != 'site' && (new $model_name())->verGroup) {
                if(gettype($params) == 'object'){
                    $model = $params;
                } else{
                    $model = $model_name::find()->where(['id'=>$params['id']])->one();
                }
            }

            $query_rules = Rule::find()
                ->where(['path' => $app_path, 'controller' => $controller, 'status' => 1]);

            if ($model !== null && !ControllerCommon::isAdmin()) {
                $rules = $query_rules->andWhere(['group_id' => $model->group_id])->asArray()->all();
                // if($controller == 'captive')
                //     dd([$model,$rules,'path' => $app_path, 'controller' => $controller, 'status' => 1]);
            } else {
                $rules = $query_rules->andWhere(['or', ['in', 'group_id', $groups],['group_id'=> self::User()->group_id]])->asArray()->all();
                // if($controller == 'captive')
                //     dd([$model,$rules,'path' => $app_path, 'controller' => $controller, 'status' => 1]);
            }
 

            if ($rules === null) {
                return [];
            } else {
                foreach ($rules as $rule) {
                    $origins = explode(';', $rule['origin']);
                    if (in_array($remote_addr, $origins) || in_array('*', $origins)) {
                        $action_list = explode(';', $rule['actions']);
                        if ( $model == null ||ControllerCommon::isAdmin()|| ($rule['group_id'] == $model->group_id)) {
                            $actions = array_merge($actions, $action_list);
                        }
                    }
                }
            }
        }

        if (isset($actions) && !empty($actions))
            return $actions;

        return [];
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

            $groups = ControllerCommon::userGroups();
            $users_groups = UserGroup::find()->where(['in', 'group_id', $groups])->asArray()->all();
            $group_ids = [];
            foreach ($users_groups as $key => $value) {
                $group_ids[] = $value['group_id'];
            }
            $model = $model->andFilterWhere(['in', 'group_id', $group_ids]);
        }

        $model = $model->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function setKeys()
    {
        if (!Yii::$app->user->isGuest) {

            $users = User::find()->all();
            foreach ($users as $user) {
                echo "UPDATE user SET auth_key = '" . md5($user->id . $user->email . $user->level . '_auth_token_sider_piauiconectado') . "' WHERE id = " . $user->id . ";";
                echo "<br>";
            }
        }
        return false;
    }

    /*** FUNÇÕES UTILITARIAS ***/

    function actionPhpInfo()
    {
        phpinfo();
    }

    function getOS()
    {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $os_platform =   "Unknown";
        $os_array =   array(
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }
        return $os_platform;
    }

    /**
     * Kullanicinin kullandigi internet tarayici bilgisini alir.
     * 
     * @since 2.0
     */
    function getBrowser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $browser        = "Bilinmeyen Tarayıcı";
        $browser_array  = array(
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome',
            '/edge/i'       =>  'Edge',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        );

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }
        return $browser;
    }

    static function mailer()
    {

        $params = Params::findOne(1);
        $mailer = new Mailer();
        $model = $params->emailService;

        $mailer->transport = [
            'scheme' => $model->scheme,
            'host' => $model->host,
            'username' => $model->username,
            'password' => $model->password,
            'port' => $model->port,
            'enableMailerLogging' => true
            //'dsn' => "{$model->scheme}://{$model->username}:{$model->password}@{$model->host}:{$model->port}"
        ];

        return $mailer;
    }

    public function sendEmail($name, $from, $to, $subject, $message)
    {

        $URL = Yii::$app->params['rootUrl'];
        $mail = Yii::$app->mailer->compose('layouts/template', ['subject' => $subject, 'content' => $message])
            ->setFrom($from)
            ->setTo($to)
            ->setBcc('honald.silva@piauiconectado.com.br')
            ->setSubject($subject);
        //->setHtmlBody($message);

        if ($mail->send()) {
            return "email enviado";
        } else {
            return "email não enviado";
        }
    }

    public function sendEmailHtml($name, $from, $to, $subject, $message)
    {

        $URL = Yii::$app->params['rootUrl'];
        $mail = Yii::$app->mailer->compose()
            ->setFrom($from)
            ->setTo($to)
            ->setBcc('honaldcarvalhoa@gmail.com')
            ->setSubject($subject)
            ->setHtmlBody($message);

        if ($mail->send()) {
            return "email enviado";
        } else {
            return "email não enviado";
        }
    }

    public function sanitizeString($str)
    {
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = $this->sanatizeReplace($str);
        $str = preg_replace('/[^a-z0-9]/i', '_', $str);
        $str = preg_replace('/_+/', '-', $str);
        return $str;
    }

    public function sanatizeReplace($str)
    {
        $removeItens = ["[", "]", ",", "(", ")", ";", ":", "|", "!", "\"", "$", "%", "&", "#", "=", "?", "~", ">", "<", "ª", "º", "-"];
        $str = preg_replace("#[/]#", '_', $str);
        foreach ($removeItens as $item) {
            $str = preg_replace('/[' . $item . ']/', '_', $str);
        }
        return $str;
    }

    public function sanatizeNoReplace($str)
    {
        $removeItens = ["[", "]", ",", "(", ")", ";", ":", "|", "!", "\"", "$", "%", "&", "#", "=", "?", "~", ">", "<", "ª", "º", "-"];
        $str = preg_replace("#[/]#", '_', $str);
        foreach ($removeItens as $item) {
            $str = preg_replace('/[' . $item . ']/', '', $str);
        }
        return $str;
    }

    public function formatBytes($bytes, $precision = 2, $show_unit = true)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 
        if ($show_unit) {
            return round($bytes, $precision) . ' ' . $units[$pow];
        } else {
            return ['value' => round($bytes, $precision), 'unit' => $units[$pow]];
        }
    }

    function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function dataDiff($date_begin, $date_end)
    {
        $origin = strtotime($date_begin);
        $target = strtotime($date_end);
        $diff = $target - $origin;
        return $diff;
    }

    
}
