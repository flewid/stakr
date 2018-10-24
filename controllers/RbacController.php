<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 12/23/14
 * Time: 2:04 PM
 */

namespace app\controllers;

use app\components\rbac\OwnRule;
use app\models\User;
use Yii;
use app\components\Controller;
use \app\components\rbac\RoleRule;

class RbacController extends Controller{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); //удаляем старые данные

        //Создадим для примера права для доступа к админке
        $adminPermission = $auth->createPermission('adminPermission');
        $adminPermission->description = 'Админ permission';
        //$panelPermission->ruleName=''; //можно не делать, потому что он будет включен в adminRole
        $auth->add($adminPermission);

        $userPermission = $auth->createPermission('userPermission');
        $userPermission->description = 'Пользователь пермишн';
        //$panelPermission->ruleName=''; //можно не делать, потому что он будет включен в adminRole
        $auth->add($userPermission);

        $ownRule = new OwnRule();
        $auth->add($ownRule);
        $ownPermission = $auth->createPermission('updateOwnModel');
        $ownPermission->description = 'Редактировать свою запись';
        $ownPermission->ruleName=$ownRule->name;
        $auth->add($ownPermission);

        //Включаем наш обработчик
        //$commonRule = new RoleRule();
        //$auth->add($commonRule);
        //Добавляем роли
        $userRole = $auth->createRole(User::ROLE_USER);
        $userRole->description = 'Пользователь';
        //$userRole->ruleName = $commonRule->name;//какой обработчик должен работать
        $auth->add($userRole);

        $adminRole = $auth->createRole(User::ROLE_ADMIN);
        $adminRole->description = 'Администратор';
        //$adminRole->ruleName = $commonRule->name;//какой обработчик должен работать
        $auth->add($adminRole);

        $auth->addChild($userRole, $ownPermission);
        $auth->addChild($userRole, $userPermission);
        $auth->addChild($adminRole, $adminPermission);

        $auth->addChild($adminRole, $userRole);
    }
} 