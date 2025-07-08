<?php

namespace weebz\yii2basics\models;

use Yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "rules".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $group_id
 * @property string $path
 * @property string $controller
 * @property string $actions
 * @property string $origin
 * @property int|null $status
 *
 * @property Groups $group
 * @property Users $user
 */
class Rule extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id', 'status'], 'integer'],
            [['controller', 'actions'], 'required'],
            [['controller'], 'string', 'max' => 255],
            [['origin'], 'string'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'controller' => Yii::t('app', 'Controller'),
            'actions' => Yii::t('app', 'Actions'),
            'origin' => Yii::t('app', 'Origin'),
            'status' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    
    public function getControllers()
    {
        $controllers = $controllers_array = $controllers_actions = [];
    
        $controllers_weebz = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@vendor/weebz/yii2-basics/src/controllers'));
        $controllers_app = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@app/controllers'));
        $controllers_app_rest = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@app/controllers/rest'));
        $controllers_app_custom = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@app/controllers/custom'));
    
        $file_lists = [
            'app' => $controllers_app,
            'app/rest' => $controllers_app_rest,
            'app/custom' => $controllers_app_custom,
            'weebz/controllers' => $controllers_weebz,
        ];
    
        foreach ($file_lists as $key => $list) {
            foreach ($list as $controller) {
                $controller_name = Inflector::camel2id(substr(basename($controller), 0, -14));
    
                if (!empty($controller_name)) {
                    $contents = file_get_contents($controller);
    
                    // Garante que cada categoria tenha sua própria chave única
                    $controller_key = "{$key}:{$controller_name}";
    
                    // Adiciona o controller ao array de controllers
                    $controllers[$controller_key] = "{$key}/{$controller_name}";
                    $controllers_array[] = ['id' => $controller_key, 'name' => "{$key}/{$controller_name}"];
    
                    // Inicializa a entrada para garantir que ela não seja sobrescrita
                    if (!isset($controllers_actions[$controller_key])) {
                        $controllers_actions[$controller_key] = [];
                    }
    
                    // Captura as ações do controller
                    preg_match_all('/public function action(\w+?)\(/', $contents, $result);
    
                    foreach ($result[1] as $action) {
                        $add = Inflector::camel2id($action);
                        if ($add !== 's') {
                            $controllers_actions[$controller_key][$add] = $add;
                        }
                    }
                }
            }
        }

        return [
            'controllers' => $controllers,
            'controllers_actions' => $controllers_actions,
            'controllers_array' => $controllers_array
        ];
    }
    
    
    
}