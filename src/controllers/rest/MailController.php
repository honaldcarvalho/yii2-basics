<?php

namespace weebz\yii2basics\controllers\rest;

use Yii;
use Exception;
use yii\symfonymailer\Mailer;
use weebz\yii2basics\models\Params;
use weebz\yii2basics\controllers\AuthController;

class MailController extends ControllerRest {

    static function sendEmail($subject,$from,$to,$message) {

       $isValid = false;

        if(isset($subject) && isset($from) && isset($to) && isset($message)){   
            $isValid = true;
        }

        if($isValid) {

            $params = Params::get();
            $mailer = new Mailer;
            $emailService = $params->getEmailService()->one();

            $mailer->transport = [
                'scheme' => $emailService->scheme,
                'host' => $emailService->host,
                'encryption' => $emailService->enable_encryption ? $emailService->encryption : '',
                'username' => $emailService->username,
                'password' => $emailService->password,
                'port' => $emailService->port,
                'enableMailerLogging'=>true
            ];

            $alert_info = Yii::t('app',"This message was sent automatically by the system {title}, don't answer.",
            [
                'title'=> $params->title
            ]);

            $scheme = $_SERVER['REQUEST_SCHEME'];
            $url = $_SERVER['HTTP_HOST'];  
            $uri = $_SERVER['REQUEST_URI'];

            $content = <<< HTML
                <tr>
                    <td>
                    <p>Hi,</p>
                    <p>{$params->title}</b></p>
                        
                    <table role='presentation' border='0' cellpadding='0' cellspacing='0' class='btn btn-primary'>
                        <tbody>
                        <tr>
                            <td align='center'>
                            <table border='0' cellpadding='0' cellspacing='0' role='presentation' style='box-sizing:border-box'>
                                <tbody>
                                    <tr>
                                        <td style='box-sizing:border-box'>
                                            <a href='{$scheme}://{$url}{$uri}' target='_blank' rel='noopener noreferrer' data-auth='NotApplicable' class='x_button x_button-primary' style='box-sizing:border-box; border-radius:4px; color:#fff; display:inline-block; overflow:hidden; text-decoration:none; background-color:#2d3748; border-bottom:8px solid #2d3748; border-left:18px solid #2d3748; border-right:18px solid #2d3748; border-top:8px solid #2d3748' data-safelink='true' data-linkindex='1'>Visualizar</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p><small> {$alert_info} </small></p>
                    </td>
                </tr>
            HTML;

            $message = $mailer->compose('@vendor/weebz/yii2-basics/src/mail/layouts/template', [
                'subject' => $subject,
                'content' => $content
            ]);
            $response = $message->setFrom($from)->setTo($to)
            ->setSubject($subject)
            ->send();

            if($response) {
                return [
                    'code'=>200,
                    'success'=>true,
                    'message'=>
                    Yii::t('app',"Email sended to {email}.  See you email.",
                    [
                        'email'=> $params->email
                    ])
                ];
            }else{
                foreach (Yii::getLogger()->messages as $key => $message) {
                    if($message[2] == 'yii\symfonymailer\Mailer::sendMessage'){
                        return [
                            'code'=>200,
                            'success'=>false,
                            'message'=>
                            Yii::t('app',"Occoured some error: {message}.",
                            [
                                'message'=> $message[0]
                            ])
                        ];
                    }
                }

            }
        }

        return [
            'code'=>400,
            'success'=>false,
            'message'=> Yii::t('app',"Bad Request.")
        ];

    }

    public function actionSend()
    {

        if($this->request->isPost){

            $post = $this->request->post();

            $isValid = true;
            $from = $post['from'] ?? $isValid = false;
            $to = $post['to'] ?? $isValid = false;
            $subject = $post['subject'] ?? $isValid = false;
            $message = $post['message'] ?? $isValid = false;
            
            if($isValid){
                return self::sendEmail($subject,$from,$to,$message);
            } else {
                return throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Bad Request. Params fault!'));
            }      
        }

        throw new \yii\web\MethodNotAllowedHttpException(Yii::t('app', 'Method Not Allowed.'));
    }
}