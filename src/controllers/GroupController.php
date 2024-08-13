<?php

namespace weebz\yii2basics\controllers;;

use weebz\yii2basics\models\Group;
use weebz\yii2basics\models\GroupSearch;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use weebz\yii2basics\models\UserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
/**
 * GroupController implements the CRUD actions for Group model.
 */
class GroupController extends ControllerCommon
{
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
        $this->access = $this->getAccess($id); 
         $this->free = ['responder','editar'];
    }

    /**
     * Lists all Group models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Group model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $users = new \yii\data\ActiveDataProvider([
            'query' => UserGroup::find()->where(['group_id'=>$id]),
            'pagination' => false,
         ]);
 
        return $this->render('view', [
            'model' => $this->findModel($id),
            'users' => $users
        ]);
    }

    /**
     * Creates a new Group model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Group();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Group model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Group model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $this->findModel($id)->delete();
            \Yii::$app->session->setFlash('success', 'Group deleted');
            return $this->redirect(['index']);
        } catch (\Throwable $th) {
            $message = '';
            if($th->errorInfo[0] == "23000"){
                $message = Yii::t('app','In use!');
            }
            \Yii::$app->session->setFlash('danger', "Can't deleted group. {$message}");
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    /**
     * Finds the Group model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Group the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Group::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
