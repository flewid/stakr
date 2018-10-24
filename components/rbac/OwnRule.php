<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 12/23/14
 * Time: 1:52 PM
 */

namespace app\components\rbac;


use app\models\Log;
use Yii;
use yii\rbac\Rule;
use yii\helpers\ArrayHelper;
use \app\models\User;
//use common\models\User;
class OwnRule extends Rule
{
    public $name = 'ownRule';
    //execute делает форич из roles в acces control
    public function execute($userID, $item, $params)
    {
        $roleOrPermisstion=$item->name;//точно updateOwnModel
        if($userModel = User::findOne($userID))
        {
            if($userModel->role==User::ROLE_ADMIN)
                return true;
            //if($userModel->role == User::ROLE_MODER)
                //return true;
            if(isset($params['model']))
            {
                if($userModel->role == User::ROLE_MODER)
                    return $params['model']->author_id==Yii::$app->user->id || $params['model']->manager_id==Yii::$app->user->id;

                if($params['model'] instanceof Log)
                {
                    if($userModel->role == User::ROLE_USER)
                        return $params['model']->user_id==Yii::$app->user->id;
                }
                if($userModel->role == User::ROLE_USER)
                    return $params['model']->author_id==Yii::$app->user->id;
            }
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