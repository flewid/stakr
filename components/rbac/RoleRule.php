<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 12/23/14
 * Time: 1:52 PM
 */

namespace app\components\rbac;

/*
Генерируемые файлы которые делает rbacController
assignments
user_id=>role

items тоже генериться
rules тоже
*/
use Yii;
use yii\rbac\Rule;
use \app\models\User;

class RoleRule extends Rule
{
    public $name = 'roleRole';
    //execute делает форич из roles в acces control
    public function execute($userID, $item, $params)
    {
        $roleOrPermisstion=$item->name;//роль или название пермишн
        if($userModel = User::findOne($userID))
        switch($roleOrPermisstion)
        {
            case User::ROLE_ADMIN:
            {
                return $userModel->role == User::ROLE_ADMIN;
                break;
            }
            case User::ROLE_USER://туда кудо можно ROLE_USER может и админ тоже
            {
                if($userModel->role == User::ROLE_ADMIN)
                    return true;
                if($userModel->role == User::ROLE_MODER)
                    return true;
                return $userModel->role == User::ROLE_USER;
                break;
            }
            /*
            case 'panel':{
                some additional logic
            }
            */
        }
        return false;
    }
}

/*
if(\Yii::$app->user->can('createUser')){
     //представление с формой для создания пользователей
}else{
     //представление показывающее пользователю ошибку
}
public function actionAbout()
{
    if (!\Yii::$app->user->can('about')) {
        throw new ForbiddenHttpException('Access denied');
    }
    return $this->render('about');
}
public function beforeAction($action)
{
    if (parent::beforeAction($action)) {
        if (!\Yii::$app->user->can($action->id)) {
            throw new ForbiddenHttpException('Access denied');
        }
        return true;
    } else {
        return false;
    }
}
 */