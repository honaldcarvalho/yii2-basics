<?php

namespace tests\unit\controllers;

use Yii;
use weebz\yii2basics\controllers\AuthorizationController;
use weebz\yii2basics\models\Role;
use weebz\yii2basics\models\User;
use weebz\yii2basics\models\UserGroup;
use yii\web\Application;

class AuthorizationControllerTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        // Simula usuário logado
        $user = User::findOne(1);
        Yii::$app->user->login($user);
    }

    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testAdminAccessAlwaysAllowed()
    {
        // Simula usuário administrador
        Yii::$app->user->login(User::find()->where(['id' => 1])->one()); // Assumindo que id=1 é admin

        $result = AuthorizationController::verAuthorization('site', 'index');
        $this->assertTrue($result);
    }

    public function testPageAuthWithValidRole()
    {
        Yii::$app->controller->id = 'notification';
        Yii::$app->controller->action = (object)['id' => 'index'];

        $auth = new AuthorizationController('auth', Yii::$app);

        $this->assertTrue($auth->pageAuth());
    }

    public function testVerAuthorizationWithValidRole()
    {
        $result = AuthorizationController::verAuthorization(
            'weebz\yii2basics\controllers\NotificationController',
            'index'
        );
        $this->assertTrue($result);
    }

    public function testVerAuthorizationFailsWithoutRole()
    {
        $result = AuthorizationController::verAuthorization(
            'nonexistent\FakeController',
            'destroy'
        );
        $this->assertFalse($result);
    }

    public function testVerAuthorizationFailsWithoutValidLicense()
    {
        // Força verificar sem licença
        $user = User::findOne(1);
        Yii::$app->user->login($user);

        $role = new Role([
            'controller' => 'site',
            'actions' => 'view',
            'group_id' => 999,
            'status' => 1,
            'origin' => '*',
        ]);
        $role->save(false);

        $this->assertFalse(AuthorizationController::verAuthorization('site', 'view'));
    }
}
