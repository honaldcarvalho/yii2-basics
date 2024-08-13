<?php

namespace weebz\yii2basics\modules\common\controllers;

use weebz\yii2basics\modules\common\controllers\ControllerCommon;
use weebz\yii2basics\modules\common\models\File;
use Yii;
use weebz\yii2basics\modules\common\models\User;
use weebz\yii2basics\modules\common\models\UserGroup;
use weebz\yii2basics\modules\common\models\UserSearch;
use weebz\yii2basics\modules\common\models\Group;;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends ControllerCommon
{
    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {           
        $model = $this->findModel($id);
        $user_group =  new UserGroup();
        
        $groups_free_arr = [];
        $group_selecteds = [];

        foreach (UserGroup::find()->select('group_id')->where(['user_id'=>$model->id])->asArray()->all() as $group_selected){
            $group_selecteds[] = $group_selected['group_id'];
        }

        $groups_free = Group::find(["status"=>1])->select('id,name')->where(['not in','id', 
                           $group_selecteds
                        ])->all();

        foreach ($groups_free as $group_arr){
            $groups_free_arr[$group_arr['id']] = $group_arr['name'];
        }
        
        $groups = new \yii\data\ActiveDataProvider([
           'query' => UserGroup::find()->where(['user_id'=>$id]),
           'pagination' => false,
        ]);

        return $this->render('view', [
            'groups'=>$groups,
            'groups_free_arr'=>$groups_free_arr,
            'group_selecteds'=>$group_selecteds,
            'user_group'=>$user_group,
            'model' => $model,
        ]);
    }

    public function actionProfile($id)
    {           
        $model = $this->findModel($id);

        return $this->render('profile',
            ['model' => $model]
        );
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User();
        $group = null;


        if ($this->request->isPost) {
            $post = $this->request->post();
            if ($model->load($post)) {

                if(Yii::$app->request->get('id') !== null || isset($post['User']['group_id'])){
                    if(Yii::$app->request->get('id') !== null) {
                        $group_query = Yii::$app->request->get('id');
                    } else {
                        $group_query = $post['User']['group_id'];
                    }
                    $group = Group::find()->where(['id'=>$group_query])->orWhere(['name'=>$group_query])->one();
                    if($group !== null) {
                        $model->group_id = $group->id;
                    } else {
                        $group = new Group();
                        $group->name = $group_query;
                        $group->save();
                        $model->group_id = $group->id;
                    }
                }
                
                $name_array = explode(' ', $model->fullname);
                $model->username = strtolower($name_array[0] . '_' . end($name_array)).'_'.Yii::$app->security->generateRandomString(8);

                $model->setPassword($model->password);
                $model->generateAuthKey();

                if($model->validate() && $model->save()){
                    Yii::$app->session->setFlash('success', 'User created as success! ');

                    if($group){
                        $ug = new UserGroup();
                        $ug->user_id = $model->id;
                        $ug->group_id = $group->id;
                        $ug->save();
                    }

                    if(Yii::$app->request->get('id') !== null ){
                         return $this->redirect(["group/{$model->group_id }"]);
                    }else{
                        return $this->redirect(['index']);
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'group' => $group
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = User::findOne($id);
        $model->scenario = User::SCENARIO_UPDATE;
        $model->username_old = $model->username;
        $model->email_old = $model->email;

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $model->resetPassword();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionEdit()
    {
        $model = $this->findModel(Yii::$app->user->id);
        $model->scenario = User::SCENARIO_EDIT;
        $model->username_old = $model->username;
        $model->email_old = $model->email;

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $model->resetPassword();
            return $this->redirect(['profile', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
        
    }
    
    public function actionAddGroup()
    {

        $post = Yii::$app->request->post();
        $i = 1;
        foreach($post['UserGroup']['group_id'] as $group){
            $model = new UserGroup();
            $model->user_id = $post['UserGroup']['user_id'];
            $model->group_id = $group;
            $model->save();
            $i++;
        }

        return $this->redirect(['user/view/'.$model->user_id]);
    }
    
    public function actionRemoveGroup($id)
    {
        $model = UserGroup::findOne(['id'=>$id]);
        $user_id = $model->user_id;
        $model->delete();        
                
        return $this->redirect(['/user/view/'.$user_id]);
    }
    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if($id != 1){
            $user = $this->findModel($id);
            if($user->file_id !== null){
                $picture = File::findOne($user->file_id);
                if(file_exists($picture->path)){
                    @unlink($picture->path);
                    if($picture->pathThumb){
                        @unlink($picture->pathThumb);
                    }
                }
            }
            $delete = $user->delete();
        }else{
            \Yii::$app->session->setFlash('error', 'Is not possible exclude master user');
        }
        if($delete){
            \Yii::$app->session->setFlash('success', 'User removed');
        }
        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if(isset(Yii::$app->user->identity) && !$this::isAdmin()){
            return Yii::$app->user->identity;
        }else if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
