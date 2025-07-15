<?php

namespace weebz\yii2basics\controllers;

use Yii;
use weebz\yii2basics\models\SiteSection;
use weebz\yii2basics\controllers\AuthController;
use yii\web\NotFoundHttpException;


/**
 * SiteSectionController implements the CRUD actions for SiteSection model.
 */
class SiteSectionController extends AuthController
{

    /**
     * Lists all SiteSection models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SiteSection();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,['orderBy'=>['order' => SORT_ASC]]);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SiteSection model.
     * @param integer $id
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
     * Creates a new SiteSection model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SiteSection();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SiteSection model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SiteSection model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionOrder()   {
        $items = [];
        $resuts = [];

        if (Yii::$app->request->isPost) {
            $items = $_POST['items'];
            foreach ($items as $key => $value) {               
                $resuts[$value]  = Yii::$app->db->createCommand()->update('site_sections', ['order' => $key + 1], "id = {$value}")->execute();
            }
        }
        return \yii\helpers\Json::encode(['atualizado'=>$resuts]);
    }

}
