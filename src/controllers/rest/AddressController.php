<?php

namespace weebz\yii2basics\controllers\rest;
use Yii;
use weebz\yii2basics\controllers\rest\ControllerCustom;
use weebz\yii2basics\models\City;
use weebz\yii2basics\models\State;

class AddressController extends AuthController {
    
    public function __construct($id, $module, $config = array())
    {
        parent::__construct($id, $module, $config);
        $this->free = ['cities', 'states'];
    }

        public function actionCities(){
            $body = Yii::$app->request->getBodyParams();
        return City::findAll(['state_id'=>$body['state_id'], 'status'=>1]);
    }

    public function actionStates(){
        return State::findAll(['status'=>1]);
    }

}