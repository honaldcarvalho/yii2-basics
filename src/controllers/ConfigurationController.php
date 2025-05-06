<?php

namespace weebz\yii2basics\controllers;

use weebz\yii2basics\controllers\rest\ControllerCustom;
use weebz\yii2basics\controllers\rest\StorageController;
use Yii;
use weebz\yii2basics\models\Configuration;
use weebz\yii2basics\models\Parameter;
use yii\web\NotFoundHttpException;

/**
 * ConfigurationController implements the CRUD actions for Configuration model.
 */
class ConfigurationController extends AuthController
{
    /**
     * {@inheritdoc}
     */
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);;
    }
    /**
     * Lists all Configuration models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new Configuration();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Configuration model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Configuration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Configuration();

        if ($model->load(Yii::$app->request->post())){ 
            
            $file = \yii\web\UploadedFile::getInstance($model, 'file_id');

            if(!empty($file) && $file !== null){

                $arquivo = StorageController::uploadFile($file,['save'=>true]);

                if ($arquivo['success'] === true) {
                    $model->file_id = $arquivo['data']['id'];
                }
            }

            $count = Configuration::find(['id'])->count();
            $model->posicao = $count + 1;

            if($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);

    }

    /**
     * Updates an existing Configuration model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);
        $old = $model->file_id;
        $changed = false;
        $post = Yii::$app->request->post(); 

        if ($model->validate() && $model->load($post)) { 

            $file = \yii\web\UploadedFile::getInstance($model, 'file_id');
            if(!empty($file) && $file !== null){
                $file = StorageController::uploadFile($file,['save'=>true,'thumb_aspect'=>'320/198']);
                if ($file['success'] === true) {
                    $model->file_id = $file['data']['id'];
                    $changed = true;
                }
            } else if(isset($post['remove']) && $post['remove'] == 1){
                $model->file_id = null;
                $changed = true;
            } 

            if(!$changed){
                $model->file_id = $old;
            }
            
            if($model->save()) {
                if($changed){
                    StorageController::removeFile($old);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }

        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    /**
     * Deletes an existing Configuration model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if($id != 1){
            $this->findModel($id)->delete();
        }else{
            \Yii::$app->session->setFlash('error', 'Is not possible exclude initial Configuration');
        }

        return $this->redirect(['index']);
    }

}
