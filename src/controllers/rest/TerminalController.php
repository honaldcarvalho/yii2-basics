<?php

namespace weebz\yii2basics\controllers\rest;
use Yii;
use weebz\yii2basics\controllers\rest\Controller;
use weebz\yii2basics\models\custom\OnuUncfg;
use weebz\yii2basics\models\Ssh2;

class TerminalController extends AuthController {

    public function actionGetUnconfig(){
        $items = [];
        $body = (object) Yii::$app->request->getBodyParams();
        
        $group_id = $body->group_id ?? null;
        $host = $body->host ?? null;
        $port = $body->port ?? 22;
        $hostname = $body->hostname ?? null;
        $password = $body->password ?? null;
        $username = $body->username ?? null;

        $result = $this->getUnconfigZTE($group_id,$host,$hostname,$username,$password,$port);
    
        return $result;
    }


    function getUnconfigZTE($group_id,$host,$hostname,$username,$password,$port){

        // $sshTool = new Ssh2($host, $username, $password, $port);

        // if ($sshTool == null || ($sshTool !== null && $sshTool->conn == null)) {
        //     throw new \yii\web\ServerErrorHttpException();
        // }
        $models = [];
        $start = date('Y-m-d H:i:s:u e');

        //$pon_results = $sshTool->runiShell('switch virtual-network-device acessenet');
        //$pon_results = ['gpon-olt_1/1/5      WKE2.119.241R2B      ZYWN671FD9AC       DEFAULT'];//$sshTool->runiShell('show gpon onu uncfg');
        $pon_results = ['gpon-olt_1/1/6      XEI2.119.500R2A      ZYWN853FX5B       DEFAULT'];//$sshTool->runiShell('show gpon onu uncfg');
        //$pon_results = ['gpon-olt_1/1/5      WKE2.119.241R2B      ZYWN671FD9AC       DEFAULT','gpon-olt_1/1/6      XEI2.119.500R2A      ZYWN853FX5B       DEFAULT'];//$sshTool->runiShell('show gpon onu uncfg');
        //$pon_results = [];
        $seriais = [];
        $serial = null;

        foreach ($pon_results as $pon_result) {
    
            if ( strpos($pon_result, 'gpon-olt') !== false ) {

                $onu = trim(substr($pon_result, 0, 20));
                $link_status = '-';
                $model = trim(substr($pon_result, 20, 15));
                $serial = trim(substr($pon_result, 41, 18));
                $profile_status = trim(substr($pon_result, 59, 10));
                
                //$print [] =  "ONU {$onu} | Profile Name {$model} | Profile Name {$serial} | Profile status {$profile_status}\n";

                $onuModel = new OnuUncfg();
                $seriais[] = $serial;

                if (($find = OnuUncfg::find()->where(['serial' => $serial])->one()) === null){
                    $onuModel->group_id = $group_id;
                    $onuModel->host = $host;
                    $onuModel->model = $model;
                    $onuModel->hostname = $hostname;
                    $onuModel->onu = $onu;
                    $onuModel->link_status = $link_status;
                    $onuModel->serial = $serial;
                    $onuModel->profile_status = $profile_status;
                    $onuModel->status = 0;
                    $models[] = $onuModel;
                    $onuModel->save();
                }else {
                    $find->status = 0;
                    $models[] = $find;
                    $find->save();
                }
                
            }
        }
    
        // //verifica se a ONU já esta cadastrada, caso sim, verifica se ela foi provisionada ou não
        Yii::$app->db->createCommand()
        ->update('tbln_onus_uncfgs', ['status'=>1], ['not in','serial',$seriais])
        ->execute();

        $end = date('Y-m-d H:i:s:u e');

        return ['start'=> $start, 'end'=> $end, 'models'=>$models, OnuUncfg::find()->all()];
    
    }
    

}