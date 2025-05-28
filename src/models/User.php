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
        return Group::getAllDescendantIds($this->getGroups()->select('id')->column());
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
