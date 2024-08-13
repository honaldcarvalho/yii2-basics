<?php

namespace weebz\yii2basics\controllers\rest;

use Yii;

use weebz\yii2basics\controllers\Controller as ControllerCommon;
use weebz\yii2basics\models\custom\Patient;

class ControllerCustom extends \yii\rest\Controller
{
    public $enableCsrfValidation = false;
    public $origin;
    public $free = [];
    
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {   
        parent::init();

        \Yii::$app->user->enableSession = false;
        $this->origin = ['*'];
    
    }

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    public function bearerAuth(){
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!in_array(Yii::$app->controller->action->id, $this->free)) {

            $user_find = null;
            
            if(isset(Yii::$app->request->headers["authorization"])) {
                $token = Yii::$app->request->headers["authorization"];
                [$type,$value] = explode(' ',$token);
                if($type == 'Basic'){
                    $decoded = base64_decode($value);
                    [$username,$password] = explode(":",$decoded);
                    $user_find = Patient::find()->select('id,fullname')
                    ->where(['password'=>md5($password),'status'=>Patient::STATUS_ACTIVE])
                    ->andWhere(['or',
                        ['username'=>$username],
                        ['email'=>$username]
                    ])
                    ->one();
                }else{
                    $user_find = Patient::find()->select('id')->where(['access_token'=>$value])
                    ->andWhere(['status'=>true])
                    ->one();
                }
                if (!$user_find) {
                    throw new \yii\web\ForbiddenHttpException('Você não tem permissão a esse recurso.');
                }
            }
        
            return $user_find;
        }
        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => yii\web\Response::FORMAT_JSON,
            ],           
        ];
        return $behaviors;
    }

    public function behaviors()
    {
        
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => yii\web\Response::FORMAT_JSON,
            ],           
        ];
        
        $this->bearerAuth();
        // $behaviors['headers'] = [
        //     'class' => \yii\filters\Cors::className(),
        //     'cors' => [
        //         // restrict access to
        //         'Access-Control-Allow-Origin' => ['*'],
        //         'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        //         // Allow only POST and PUT methods
        //         'Access-Control-Request-Headers' => ['*'],
        //         // Allow only headers 'X-Wsse'
        //         'Access-Control-Allow-Credentials' => true,
        //         // Allow OPTIONS caching
        //         'Access-Control-Max-Age' => 86400,
        //         // Allow the X-Pagination-Current-Page header to be exposed to the browser.
        //         'Access-Control-Expose-Headers' => [],
        //     ]
        // ];
        // $headers = Yii::$app->response->headers;
        // $headers->set ('Access-Control-Allow-Origin', ['*']);
        // $headers->set ('Access-Control-Allow-Credentials', 'true');
        // $headers->set ('Access-Control-Max-Age', '84000');
        // $headers->set ('Access-Control-Allow-Headers',  ['*']);
        // $headers->set ('Access-Control-Request-Headers',  ['*']);
        // $headers->set ('Access-Control-Request-Headers',  ['*']);
        // $headers->set ('Access-Control-Allow-Methods', 'GET, OPTIONS, POST, DELETE, PUT, HEAD, PATCH');

        return $behaviors;
    }

    public function getPermission($controller, $action)
    {
        $crtl = new ControllerCommon(0,0);
        return $crtl->getPermission($controller, $action);
    }

    function dataDiff($date_begin, $date_end) {
        $diff = strtotime($date_end) - strtotime($date_begin);
        $days = floor($diff / (60 * 60 * 24));
        return $days;
    }
    
    function secondsToHours($seconds) {
        $secs = $seconds % 60;
        $hrs = $seconds / 60;
        $mins = $hrs % 60;
        $hrs = $hrs / 60;
        $hora = sprintf("%02d", $hrs) . ":" . sprintf("%02d", $mins) . ":" . sprintf("%02d", $secs);
        return $hora;
    }
    
    function dataDiffUnix($date_begin, $date_end) {
        $diff = strtotime(gmdate("Y-m-d", $date_end)) - strtotime(gmdate("Y-m-d", $date_begin));
        $days = floor($diff / (60 * 60 * 24));
        return $days;
    }
    
    function dataToTimestamp($data) {
        $ano = substr($data, 6, 4);
        $mes = substr($data, 3, 2);
        $dia = substr($data, 0, 2);
        return mktime(0, 0, 0, intval($mes), intval($dia), intval($ano));
    }

    function countWorkDays($date_begin,$date_end) {

        $date_begin_p = explode('-',$date_begin);
        $date_end_p = explode('-',$date_end);

        $i = 0;

        $first_day = explode(' ',$date_begin_p[2])[0];
        $last_day = explode(' ',$date_end_p[2])[0];
        $month = $date_begin_p[1];
        $year = intval($date_begin_p[0]);
        $total = 0;

        for($i = $first_day; $i <= $last_day;$i++){

            $data = intval($i). '/'.$month. '/'.$year;
            $week_day= date("w", $this->dataToTimestamp($data));

            if($week_day<> 6 && $week_day<> 0){
                $total++;
            }
        }
        return $total;
    }

    function countBussinessDays($from, $to) {
        $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
        $holidayDays = ['*-12-25', '*-01-01', '2013-12-23']; # variable and fixed holidays
    
        $from = new \DateTime($from);
        $to = new \DateTime($to);
        $to->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $periods = new \DatePeriod($from, $interval, $to);
    
        $days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), $workingDays)) continue;
            if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
            if (in_array($period->format('*-m-d'), $holidayDays)) continue;
            $days++;
        }
        return $days;
    }

    public function asBits($fileSizeInBytes){
        $i = -1;
        $s = 0;
        if($fileSizeInBytes === 0 || $fileSizeInBytes === '0'){
            return '0 Kbps';
        }
        $byteUnits = [
          " Kbps",
          " Mbps",
          " Gbps",
          " Tbps",
          " Pbps",
          " Ebps",
          " Zbps",
          " Ybps"
        ];

        do {
          if(is_infinite($fileSizeInBytes / 1000)){
              return "INFINITO";
              break;
          }
          $fileSizeInBytes = $fileSizeInBytes / 1000;
          $i++;
          $s++;
        } while ($fileSizeInBytes > 1000);

        return number_format(max($fileSizeInBytes, 0.1), 2, '.', '') . $byteUnits[$i];
    }

}