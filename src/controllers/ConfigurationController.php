<?php

namespace weebz\yii2basics\controllers;

use weebz\yii2basics\controllers\rest\ControllerCustom;
use weebz\yii2basics\controllers\rest\StorageController;
use Yii;
use weebz\yii2basics\models\Configuration;
use weebz\yii2basics\models\MetaTag;
use weebz\yii2basics\models\Parameter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ConfigurationController implements the CRUD actions for Configuration model.
 */
class ConfigurationController extends AuthController
{
    /**
     * {@inheritdoc}
     */
    public function __construct($id, $module, $config = array())
    {
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

        if ($model->load(Yii::$app->request->post())) {

            $file = \yii\web\UploadedFile::getInstance($model, 'file_id');

            if (!empty($file) && $file !== null) {

                $arquivo = StorageController::uploadFile($file, ['save' => true]);

                if ($arquivo['success'] === true) {
                    $model->file_id = $arquivo['data']['id'];
                }
            }

            if ($model->save()) {
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
            if (!empty($file) && $file !== null) {
                $file = StorageController::uploadFile($file, ['save' => true]);
                if ($file['success'] === true) {
                    $model->file_id = $file['data']['id'];
                    $changed = true;
                }
            } else if (isset($post['remove']) && $post['remove'] == 1) {
                $model->file_id = null;
                $changed = true;
            }

            if (!$changed) {
                $model->file_id = $old;
            }

            if ($model->save()) {
                if ($changed) {
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
        if ($id != 1) {
            $this->findModel($id)->delete();
        } else {
            \Yii::$app->session->setFlash('error', 'Is not possible exclude initial Configuration');
        }

        return $this->redirect(['index']);
    }


    public function actionClone($id)
    {
        $original = Configuration::findOne($id);
        if (!$original) {
            throw new NotFoundHttpException('A configuração não foi encontrada.');
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Clona configuração principal
            $clone = new Configuration();
            $clone->attributes = $original->attributes;

            // Ajusta campos únicos
            if ($clone->hasAttribute('slug')) {
                $clone->slug .= '-clone-' . time();
            }
            if ($clone->hasAttribute('name')) {
                $clone->name .= ' (Clone)';
            }

            unset($clone->id);

            if (!$clone->save()) {
                throw new \Exception('Erro ao salvar configuração clonada.');
            }

            // Clona meta tags
            foreach ($original->metaTags as $meta) {
                $newMeta = new MetaTag();
                $newMeta->attributes = $meta->attributes;
                unset($newMeta->id);
                $newMeta->configuration_id = $clone->id;
                if (!$newMeta->save(false)) {
                    throw new \Exception('Erro ao clonar meta tag.');
                }
            }

            // Clona parâmetros
            foreach ($original->parameters as $param) {
                $newParam = new Parameter();
                $newParam->attributes = $param->attributes;
                unset($newParam->id);
                $newParam->configuration_id = $clone->id;
                if (!$newParam->save(false)) {
                    throw new \Exception('Erro ao clonar parâmetro.');
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Configuração clonada com sucesso.');
            return $this->redirect(['view', 'id' => $clone->id]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Erro ao clonar: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Manages i18n configuration settings.
     * @return mixed
     */
    public function actionI18n()
    {
        $model = Configuration::findOne(1);

        if (!$model) {
            throw new NotFoundHttpException('The main configuration was not found.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'i18n settings updated successfully.');
            return $this->refresh();
        }

        return $this->render('i18n', [
            'model' => $model,
        ]);
    }

    /**
     * Ajax action to check API connectivity (Ping).
     */
    public function actionI18nPing()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $config = Configuration::get();
        $url = Yii::$app->request->post('url') ?: $config->i18n_api_url;
        $token = Yii::$app->request->post('token') ?: $config->i18n_api_token;

        if (!$url) {
            return ['success' => false, 'message' => 'API URL is not configured.'];
        }

        try {
            $client = new \yii\httpclient\Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(rtrim($url, '/') . '/api/pull')
                ->addHeaders(['Authorization' => 'Bearer ' . $token])
                ->setOptions(['timeout' => 5])
                ->send();

            if ($response->isOk) {
                return ['success' => true, 'message' => 'Connected successfully!'];
            }
            
            return ['success' => false, 'message' => 'Connection failed. HTTP Code: ' . $response->statusCode];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Ajax action to login into the central API and update local token.
     */
    public function actionI18nLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $url = Yii::$app->request->post('url');
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        if (!$url || !$username || !$password) {
            return ['success' => false, 'message' => 'URL, Username and Password are required.'];
        }

        try {
            $client = new \yii\httpclient\Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl(rtrim($url, '/') . '/api/login')
                ->setData(['username' => $username, 'password' => $password])
                ->send();

            if ($response->isOk && isset($response->data['token'])) {
                $token = $response->data['token'];

                $config = Configuration::get();
                $config->i18n_api_url = $url;
                $config->i18n_api_token = $token;
                
                if ($config->save(false)) {
                    return ['success' => true, 'token' => $token, 'message' => 'Login successful! Settings updated in Database.'];
                }

                return ['success' => false, 'message' => 'Login OK, but failed to save settings.'];
            }
            
            $error = $response->data['error'] ?? 'Invalid credentials or API error.';
            return ['success' => false, 'message' => $error];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Ajax action to trigger a full translation pull from the central API.
     */
    public function actionI18nSync()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $service = new \weebz\yii2basics\services\TranslationSyncService();
            $stats = $service->pull();

            return [
                'success' => true,
                'message' => Yii::t('app', 'Sync completed. {source} new sources, {trans} translations updated.', [
                    'source' => $stats['source_added'],
                    'trans'  => $stats['translations_upserted'],
                ]),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
