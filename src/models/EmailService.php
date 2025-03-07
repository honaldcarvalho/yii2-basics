<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\controllers\AuthController;
use Yii;

/**
 * This is the model class for table "email_services".
 *
 * @property int $id
 * @property string $description
 * @property string $scheme
 * @property int|null $enable_encryption
 * @property string $encryption
 * @property string $host
 * @property string $username
 * @property string $password
 * @property int $port
 */
class EmailService extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enable_encryption', 'port'], 'integer'],
            [['host', 'username', 'password', 'port'], 'required'],
            [['description', 'scheme', 'encryption', 'host', 'username', 'password'], 'string', 'max' => 255],
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
            'scheme' => Yii::t('app', 'Scheme'),
            'enable_encryption' => Yii::t('app', 'Enable Encryption'),
            'encryption' => Yii::t('app', 'Encryption'),
            'host' => Yii::t('app', 'Host'),
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'port' => Yii::t('app', 'Port'),
        ];
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($old_password = '')
    {
        $password_hash = md5($this->password);

        if(md5($old_password) != $password_hash){
            try {
                $this->password = $password_hash;
                return true;
            } catch (\Throwable $th) {
                return false;
            }
        }
        return true;
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public static function sendEmail($subject,$from_email,$from_name,$to,$content)
    {

        $mailer =  AuthController::mailer();

        return $mailer
            ->compose('layouts/template',
                ['subject'=>$mailer->transport->getUsername(),'content'=>$content]
            )
            ->setFrom([$from_email=> $from_name])
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }

}
