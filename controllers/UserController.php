<?php

namespace app\controllers;

use app\models\MetalMint;
use app\models\ResetForm;
use Yii;
use app\models\User;
use app\models\search\UserSearch;
use yii\base\Response;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\widgets\ActiveForm;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['update','index', 'delete', 'profile', 'change-email', 'change-password', 'activate-email-change'],
                'rules' => [
                    [
                        'actions' => ['profile', 'profile-edit' , 'change-email', 'change-password', 'activate-email-change'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', ],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['update', 'delete'],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_USER],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) /*&& $_POST['ajax']===$this->id.'-form'*/)
        {
            echo ActiveForm::validate($model);
            die();
        }
    }

    public function actionChangePassword()
    {
        $model = Yii::$app->user->identity;
        $model->scenario = 'changePassword';
        if (Yii::$app->request->isAjax)
        {
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        elseif ($model->load(Yii::$app->request->post()) /*&& $model->validate()*/)
        {
            $model->password = md5($model->password_new);
            $model->save(false);
            Yii::$app->UM->send('You have successfully changed your password',
                "You have successfuly changed your password",
                $model->email,
                $model->name
            );
            Yii::$app->session->setFlash('success', "You have successfully changed your password");
            return $this->goAlert();
        }
        else
        {
            return $this->render('changePassword', [
                'model' => $model,
            ]);
        }
    }
    public function actionActivateEmailChange($authKey)
    {
        $model = \app\models\User::find()
            ->where(['authKey' => $authKey])
            ->one();

        if ($model)
        {
            $model->email=$model->newEmail;
            $model->newEmail='';
            $model->save();

            Yii::$app->UM->send('You have successfully changed you email',
                "You have successfully changed you email",
                $model->email,
                $model->name
            );
            Yii::$app->session->setFlash('success', 'You have successfully changed you email');
            return $this->goAlert();
        }
        else
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    public function actionChangeEmail()
    {
        $model = Yii::$app->user->identity;
        $model->scenario = 'changeEmail';
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->generateAuthKey();
            $model->save();
            $link = Yii::$app->getUrlManager()->createAbsoluteUrl(['user/activate-email-change', 'authKey'=>$model->getAuthKey()]);

            Yii::$app->UM->send('We have sent to your email a link for approve to change you email',
                "We have sent to your email a link for approve to change you email
                <br>
                click the link to <a href=\"{$link}\">activate your email changing</a>
                ",
                $model->email,
                $model->name
            );
            Yii::$app->session->setFlash('success', 'We have sent to your email a link for approve to change you email');
            return $this->goAlert();
        }
        else
        {
            return $this->render('changeEmail', [
                'model' => $model,
            ]);
        }
    }
    public function actionActivate($authKey)
    {
        $model = \app\models\User::find()
            ->where(['authKey' => $authKey])
            ->one();
        if ($model)
        {
            $model->enable=1;
            $model->authKey='';
            $model->save();
            Yii::$app->UM->send('You have successfuly activated your account',
                "You have successfuly activated your account",
                $model->email,
                $model->name
            );

            Yii::$app->authManager->revokeAll($model->id);
            Yii::$app->authManager->assign(Yii::$app->authManager->getRole($model->role) , $model->id);
            Yii::$app->user->login($model);
            Yii::$app->session->setFlash('success', 'Congratulates! You have successfully activate your account.');
            return $this->goAlert();
        }
        else
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    public function actionNewpassword($authKey)
    {
        $model = \app\models\User::find()
            ->where(['authKey' => $authKey])
            ->one();
        if ($model)
        {
            $password = rand(100000, 999999);
            $model->password=md5($password);
            $model->save();
            Yii::$app->UM->send('Here is your new password',
                "Here is your new password: {$password}",
                $model->email,
                $model->name
            );
            Yii::$app->session->setFlash('success', 'Congratulates! We have sent you the new password.');
            return $this->redirect('reset');
        }
        else
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    public function actionReset()
    {
        $this->layout='login';
        $model = new ResetForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $user=User::findByUsername($model->email);
            $user->generateAuthKey();
            $user->save();
            $link = Yii::$app->getUrlManager()->createAbsoluteUrl(['user/newpassword', 'authKey'=>$user->getAuthKey()]);
            Yii::$app->UM->send('Reset your password',
                "please reset your password, click the link <a href=\"{$link}\">reset</a>",
                $user->email,
                $user->name
            );
            Yii::$app->session->setFlash('success', 'We have sent to your email a link for reset your password. Do it please.');
        }
        return $this->render('reset', [
            'model' => $model,
        ]);
    }
    public function actionSignup()
    {
        $this->layout='login';
        $model = new User();
        $model->scenario='signup';
        $model->role=$model::ROLE_USER;
        $model->generateAuthKey();
        $link = Yii::$app->getUrlManager()->createAbsoluteUrl(['user/activate', 'authKey'=>$model->getAuthKey()]);
        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            Yii::$app->UM->send('Activate your account',
                "please activate your account, click the link <a href=\"{$link}\">{$link}</a>",
                $model->email,
                $model->name
            );
            Yii::$app->session->setFlash('success', 'Thank you for signing up. We have sent to your email a link for activation. Do it please.');
            /*
            Yii::$app->user->login($model);
            Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Yii::$app->user->identity->role) , Yii::$app->user->identity->id);
            return $this->redirect(['profile']);
            */
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionProfile()
    {
        return $this->render('profile', [
            'model' => Yii::$app->user->identity,
        ]);
    }
    public function actionProfileEdit()
    {
        $model = Yii::$app->user->identity;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['profile', 'id' => $model->id]);
        } else {
            return $this->render('profileEdit', [
                'model' => $model,
            ]);
        }
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario='signup';
        $model->role=$model::ROLE_USER;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        if(Yii::$app->user->identity->id!=$id && Yii::$app->user->identity->role!=User::ROLE_ADMIN)
            throw new ForbiddenHttpException('Access denied');

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if(Yii::$app->user->identity->id!=$id && Yii::$app->user->identity->role!=User::ROLE_ADMIN)
            throw new ForbiddenHttpException('Access denied');

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
