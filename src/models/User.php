<?php

namespace weebz\yii2basics\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int|null $group_id
 * @property int|null $file_id
 * @property string|null $fullname
 * @property string|null $cpf_cnpj
 * @property int|null $language_id
 * @property string|null $theme
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property string $email
 * @property string $phone
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $access_token
 * @property string|null $token_validate
 *
 * @property File $file
 * @property Group $group
 * @property Languages $language
 * @property Log[] $logs
 * @property Rule[] $rules
 * @property UserGroups[] $userGroups
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    public $verGroup = false;
    public $email_old;
    public $cpf_cnpj_old;
    public $username_old;
    public $password = '';
    public $password_old;
    public $password_confirm;

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
    const STATUS_NOSYSTEM = 20;
    const SCENARIO_DEFAULT = 'default';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_PICTURE = 'picture';
    const SCENARIO_AUTH = 'auth';
    const SCENARIO_SEARCH = 'search';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    public function scenarios()
    {
        $scenarios[self::SCENARIO_DEFAULT] = ['fullname','email','phone','cpf_cnpj','status','fullname', 'username', 'password', 'password', 'password_confirm', 'password_reset_token', 'verification_token', 'email', 'phone', 'access_token'];
        $scenarios[self::SCENARIO_SEARCH] = ['fullname','email','phone','cpf_cnpj','status'];
        $scenarios[self::SCENARIO_UPDATE] = ['fullname','group_id','theme','email','password','password_confirm','language_id','phone'];
        $scenarios[self::SCENARIO_EDIT] = ['fullname','theme','email','password','password_confirm','language_id','phone'];
        $scenarios[self::SCENARIO_PICTURE] = ['file_id'];
        $scenarios[self::SCENARIO_AUTH] = ['access_token'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'file_id', 'language_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'email', 'phone'], 'required'],
            [['token_validate'], 'safe'],
            [['fullname','theme', 'username', 'password_hash', 'password_reset_token', 'verification_token', 'email', 'phone', 'access_token'], 'string', 'max' => 255],
            [['cpf_cnpj'], 'string', 'max' => 18],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'fullname' => Yii::t('app', 'Fullname'),
            'cpf_cnpj' => Yii::t('app', 'Cpf Cnpj'),
            'language_id' => Yii::t('app', 'Language ID'),
            'theme' => Yii::t('app', 'Theme'),
            'username' => Yii::t('app', 'Username'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'verification_token' => Yii::t('app', 'Verification Token'),
            'email' => Yii::t('app', 'Email'),
            'phone' => Yii::t('app', 'Phone'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'access_token' => Yii::t('app', 'Access Token'),
            'token_validate' => Yii::t('app', 'Token Validate'),
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $options = ['pageSize' => 10, 'orderBy' => ['id' => SORT_DESC], 'order' => false],)
    {
        $this->scenario = self::SCENARIO_SEARCH;

        $className = self::getClass();
        $table = static::tableName();
        $pageSize = 10;
        $order = false;
        $orderField = false;

        $query = static::find();

        if (isset($options['select'])) {
            $query->select($options['select']);
        }

        $sort = [
            'defaultOrder' => ['id' => SORT_DESC], // padrão
        ];

        if (isset($options['orderBy'])) {
            $sort['defaultOrder'] = $options['orderBy'];
        }

        if (isset($options['pageSize'])) {
            $pageSize = $options['pageSize'];
        }

        /**
            AQUI FAZ A VERIFICAÇÃO SE TEM UM ITEM DE ORDENAMENTO QUE MUDA A TAMANHO DA LISTAGEM. CASO SEJA FORNECIDO UM CAMPO FLAG E ELE NÃO SEJA NULO/VAZIO
            O TAMANHO PASSA PARA 10000
         */
        if (isset($options['order']) && $options['order'] && !empty($options['order']) && count($params) > 0) {
            $query->orderBy([$options['order']['field'] => SORT_ASC]);

            if (
                (
                    isset($options['order']['flag']) &&
                    $options['order']['flag'] != false &&
                    isset($params[$className][$options['order']['flag']]) &&
                    !empty($params[$className][$options['order']['flag']])
                )
            ) {
                foreach ($params["{$className}"] as $field => $search) {
                    if (!empty($search)) {
                        $pageSize = 10000;
                        break;
                    }
                }
            }
        }

        if (isset($options['join'])) {
            if (is_array($options['join'])) {
                foreach ($options['join'] as $model) {
                    [$method, $table, $criteria] = $model;
                    $query->join($method, $table, $criteria);
                }
            }
        }

        if (isset($options['groupModel'])) {
            $field =  AuthController::addSlashUpperLower($className);
            $query->leftJoin($options['groupModel']['table'], "{$table}.{$options['groupModel']['field']} = {$options['groupModel']['table']}.id");
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize
            ],
            'sort' => $sort,
        ]);

        $this->load($params);

        // grid filtering conditions
        $user = AuthController::User();

        if ($this->verGroup && $user) {
            // IDs dos grupos do usuário
            $directGroupIds = $user->getUserGroupsId();

            // IDs de todos os grupos descendentes (herdados via parent_id)
            $group_ids = Group::getAllDescendantIds($directGroupIds);

            // Se quiser sempre garantir acesso ao grupo ID 1 (admin), mantenha isso:
            $group_ids[] = 1;

            $table = static::tableName();

            // Caminho definido no modelo, se existir
            $groupPath = method_exists($this, 'groupRelationPath') ? static::groupRelationPath() : null;

            if ($groupPath) {
                $relationPath = '';
                foreach ($groupPath as $i => $relation) {
                    $relationPath .= ($i > 0 ? '.' : '') . $relation;
                    $query->joinWith([$relationPath]);
                }

                $tableAlias = Yii::createObject(static::class)->getRelation(end($groupPath))->modelClass::tableName();
                $query->andWhere(["{$tableAlias}.group_id" => $group_ids]);
            } elseif (isset($options['groupModel'])) {
                $query->andFilterWhere(['in', "{$options['groupModel']['table']}.group_id", $group_ids]);
            } elseif ($this->hasAttribute('group_id')) {
                $query->andFilterWhere(["{$table}.group_id" => $group_ids]);
            }
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        //create criteria by search type
        foreach ($params as $field => $search) {

            if ($field == 'page')
                continue;

            $field_type = gettype($search);
            $field_parts = explode(':', $field);
            if (count($field_parts) > 1) {
                [$field, $field_type] = $field_parts;
            }

            if (!isset($params["{$className}"]))
                continue;

            foreach ($params["{$className}"] as $field => $search) {

                $field_type = gettype($search);
                if (is_numeric($search) && (int)$search == $search) {
                    $field_type = "number";
                }
                $field_parts = explode(':', $field);

                if (count($field_parts) > 1) {
                    [$field, $field_type] = $field_parts;
                }

                if ($field_type == 'custom') {
                    $query->andFilterWhere(["$table.$field", $search[0], $search[1]]);
                } else if ($field_type == 'between') {
                    $query->andFilterWhere(['between', "$table.$field", $search[0], $search[1]]);
                } else if ($field_type == 'string') {
                    if (str_contains($field, 'sod') || str_contains($field, 'eod')) {
                        [$field_date, $pos] = explode('FDT', $field);
                        if ($pos == 'sod') {
                            $query->andFilterWhere(['>=', "$table.$field_date", $search]);
                        } else if ($pos == 'eod') {
                            $query->andFilterWhere(['<=', "$table.$field_date", $search]);
                        }
                    } else {
                        $query->andFilterWhere(['like', "$table.$field", $search]);
                    }
                } else if (str_contains($field, 'sod') || str_contains($field, 'eod')) {
                    [$field_date, $pos] = explode('FDT', $field);
                    if ($pos == 'sod') {
                        $query->andFilterWhere(['>=', "$table." . $field_date, $search]);
                    } else if ($pos == 'eod') {
                        $query->andFilterWhere(['<=', "$table." . $field_date, $search]);
                    }
                } else {
                    $query->andFilterWhere(["$table.$field" => $search]);
                }
            }
        }
        // $query = $dataProvider->query;
        // dd($query->createCommand()->getRawSql());
        return $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */

    public static function findIdentityByAccessToken($token, $type = null)
    {
        //return static::findOne(['auth_key' => $token]);
        return static::find()->where(['status'=>10])->andWhere(['or',['access_token'=>$token],['auth_key' => $token]])->one();
        
    }
    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])->orWhere(['email' => $username])->andWhere(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }


    /**
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
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
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * Gets query for [[Logs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(Log::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Rules]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRules()
    {
        return $this->hasMany(Rule::class, ['user_id' => 'id']);
    }

    public function getGroups()
    {
        return $this->hasMany(Group::class, ['id' => 'group_id'])
            ->viaTable('user_groups', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroup::class, ['user_id' => 'id']);
    }

    
    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public static function userGroups()
    {
        return UserGroup::find()->where(['user_id' => Yii::$app->user->identity->id]);
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return array
     */
    public function getUserGroupsId()
    {
        $groupIds = $this->getGroups()->select('id')->column();
        return Group::getAllDescendantIds($groupIds);
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this;

        if($this->password !== null && !empty($this->password)){
            $user->setPassword($this->password);
            if($user->validate()){
                $user->removePasswordResetToken();
                $user->generateAuthKey();
                return $user->save(false);
            }else{
                return false;
            }
        }
    }
    
}
