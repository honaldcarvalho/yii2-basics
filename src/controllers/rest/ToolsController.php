<?php
namespace weebz\yii2basics\controllers\rest;
use Yii;
use weebz\yii2basics\controllers\rest\Controller ;
use weebz\yii2basics\models\Log;
use weebz\yii2basics\models\User;

class ToolsController extends AuthController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'send-log' => ['POST'],
            ],
        ];
        return $behaviors;
    }
    
    // public function security($groups){
    //     if(!\weebz\yii2basics\models\Usuario::inGrupos($groups)){
    //         throw new \yii\web\ForbiddenHttpException();
    //     }     
    // }
    
    public function actionSendLog()
    {

        $body = Yii::$app->request->getBodyParams();

        $model = new Log();
        $model->user_id = $body['user_id'];
        $model->action = 'send-log';
        $model->controller = 'tools';
        $model->data = $body['data'];
        $model->save();

        return Log::findOne($model->id);

    }


}  
    
?>