<?php

namespace weebz\yii2basics\controllers;


use weebz\yii2basics\models\Log;
use weebz\yii2basics\models\Configuration;
use Yii;
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
    
    public static function getAssetsDir()
    {
        return Yii::$app->assetManager->getPublishedUrl('@vendor/weebz/yii2-basics/src/themes/adminlte3/web/dist');
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $language = null;
        $this->params = Configuration::get();

        foreach ($this->params->attributes as $key => $param) {
            Yii::setAlias("@{$key}", "$param");
        }

        $cookies = Yii::$app->request->cookies;
        $post = Yii::$app->request->post();
        
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

        return $behaviors;
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

        $params = Configuration::get();
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
