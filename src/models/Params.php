<?php

namespace weebz\yii2basics\modules\common\models;

use Yii;

/**
 * This is the model class for table "params".
 *
 * @property int $id
 * @property string $description
 * @property int $language_id
 * @property int|null $file_id
 * @property int|null $group_id
 * @property int|null $email_service_id
 * @property string $host
 * @property string $title
 * @property string $slogan
 * @property string $bussiness_name
 * @property string $email
 * @property string|null $fone
 * @property string|null $address
 * @property string|null $postal_code
 * @property int|null $ldap_login
 * @property int|null $recaptcha_login
 * @property string|null $recaptcha_secret_key
 * @property string|null $recaptcha_secret_site
 * @property string $meta_viewport
 * @property string $meta_author
 * @property string $meta_robots
 * @property string $meta_googlebot
 * @property string|null $meta_keywords
 * @property string|null $meta_description
 * @property string $canonical
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $status
 * @property int|null $logging
 *
 * @property EmailService $emailService
 * @property File $file
 * @property Language $language
 */
class Params extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'params';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'host', 'title','slogan', 'bussiness_name', 'email', 'meta_viewport', 'meta_author', 'meta_robots', 'meta_googlebot', 'canonical'], 'required'],
            [['language_id', 'file_id', 'group_id', 'email_service_id', 'ldap_login', 'recaptcha_login', 'status', 'logging'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['description', 'host', 'title', 'bussiness_name', 'email', 'fone', 'address', 'postal_code', 'recaptcha_secret_key', 'recaptcha_secret_site', 'meta_viewport', 'meta_author', 'meta_robots', 'meta_googlebot', 'meta_keywords', 'meta_description', 'canonical'], 'string', 'max' => 255],
            [['email_service_id'], 'exist', 'skipOnError' => true, 'targetClass' => EmailService::class, 'targetAttribute' => ['email_service_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'description' => Yii::t('app', 'Description'),
            'language_id' => Yii::t('app', 'Site Language'),
            'file_id' => Yii::t('app', 'Logo'),
            'background_image' => Yii::t('app', 'Background Image'),
            'group_id' => Yii::t('app', 'Client\'s Group'),
            'email_service_id' => Yii::t('app', 'Email Service'),
            'host' => Yii::t('app', 'Host'),
            'title' => Yii::t('app', 'Title'),
            'slogan' => Yii::t('app', 'Slogan'),
            'bussiness_name' => Yii::t('app', 'Bussines Name'),
            'email' => Yii::t('app', 'Email'),
            'fone' => Yii::t('app', 'Fone'),
            'address' => Yii::t('app', 'Address'),
            'postal_code' => Yii::t('app', 'Postal Code'),
            'ldap_login' => Yii::t('app', 'Ldap Login'),
            'recaptcha_login' => Yii::t('app', 'Recaptcha Login'),
            'recaptcha_secret_key' => Yii::t('app', 'Recaptcha Secret Key'),
            'recaptcha_secret_site' => Yii::t('app', 'Recaptcha Secret Site'),
            'meta_viewport' => Yii::t('app', 'Meta Viewport'),
            'meta_author' => Yii::t('app', 'Meta Author'),
            'meta_robots' => Yii::t('app', 'Meta Robots'),
            'meta_googlebot' => Yii::t('app', 'Meta Googlebot'),
            'meta_keywords' => Yii::t('app', 'Meta Keywords'),
            'meta_description' => Yii::t('app', 'Meta Description'),
            'canonical' => Yii::t('app', 'Canonical'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'status' => Yii::t('app', 'Status'),
            'logging' => Yii::t('app', 'Logging'),
        ];
    }

    /**
     * Gets query for [[EmailService]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmailService()
    {
        return $this->hasOne(EmailService::class, ['id' => 'email_service_id']);
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
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * Gets query for [[EmailService]].
     *
     * @return \yii\db\ActiveQuery
     */
    public static function get()
    {
        return self::findOne(['id' => 1]);
    }

}
