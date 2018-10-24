<?php

namespace app\controllers;

use app\models\Country;
use app\models\History;
use app\models\MetalStack;
use Yii;
use app\models\Log;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\Aida;
use app\models\User;
use yii\helpers\VarDumper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessRule;
use yii\base\InlineAction;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                //'only' => ['someAction'],
                'rules' => [
                    [
                        'actions' => ['someAction',],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['error', 'login', 'captcha', 'contact', 'logout'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'alert'],
                        'allow' => true,
                        //'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }


    public function beforeAction($action)
    {
        if($this->action->id=='error')
        {
            $this->layout = 'notLogged';
        }
        return parent::beforeAction($action);
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    //public $layout='notLogged';


    public function actionIndex()
    {
        if(Yii::$app->user->isGuest){
            $this->layout = 'notLogged';
            return $this->render('notLoggedIndex');
        }
        if(Yii::$app->user->can('adminPermission'))
        {
            return $this->render('settings');
        }
        $this->layout = 'siteMain';
        $this->initHistory();

        $todayHistories = History::find()->with('type')->today()->groupBy('metalSymbol')
            ->select(['ROUND(AVG(metalValue), 2) AS metalValue', 'metalSymbol'])->all();
        $yesterdayHistories = History::find()->with('type')->day(date('Y-m-d', time() - 24*3600))
            ->groupBy('metalSymbol')
            ->select(['ROUND(AVG(metalValue), 2) AS metalValue', 'metalSymbol'])->all();

        //$maxDate = MetalStack::find()->mine()->select(["DATE_FORMAT(MAX(metalPurchaseDate), '%Y') AS maxDate"])->createCommand()->queryScalar();
        $maxDate = date('Y');
        //$minDate = MetalStack::find()->mine()->select(["DATE_FORMAT(MIN(metalPurchaseDate), '%Y') AS maxDate"])->createCommand()->queryScalar();
        $minDate = $maxDate-4;
        $yearStacks = MetalStack::find()->mine()->open()->notInRoll()->groupByYear()->all();

        $freight = MetalStack::find()->mine()->select(["SUM(metalShippingCost)"])->createCommand()->queryScalar();
        //$cost = MetalStack::find()->mine()->select(["SUM(metalTotalPaid)"])->createCommand()->queryScalar();
        $cost = MetalStack::find()->mine()->select(["SUM(metalPurchasePrice)"])->createCommand()->queryScalar();
        $cost = $cost - $freight;
        $numismatic = MetalStack::find()->mine()->select(["SUM(total_numismatic_value)"])->createCommand()->queryScalar();
        $spot = MetalStack::find()->mine()->select(["ROUND(SUM(totalSpotPrice),4)"])->createCommand()->queryScalar();

        $last4YearStacks = MetalStack::find()->mine()->open()->notInRoll()->groupByLast4Year()->asArray()->all();

        $yearSeparateStacks = MetalStack::find()->mine()->open()->notInRoll()->groupByYearSeparate()->all();

        $weightStacks = MetalStack::find()->mine()->open()->notInRoll()->groupBySymbol()->with('type')->all();
        $countStacks = MetalStack::find()->mine()->open()->notInRoll()->groupBySymbol()->select(["metalSymbol",
            'COUNT(metalSymbol) AS count',])->with('type')->all();

        $recentLogs = Log::find()->
            where(Yii::$app->user->can('adminPermission') ? []:['user_id'=>Yii::$app->user->id,])
            ->limit(3)
            ->orderBy('id DESC')
            ->all();


        //echo Yii::$app->formatter->asDate('2015-09-24', 'full');
        //echo Yii::$app->formatter->asDate('2015-09-24', 'Y/m/d');
        /*
        echo Yii::$app->formatter->asDatetime(time(), 'php:m/d/Y H:i:s');
        echo Yii::$app->formatter->asDatetime(time(), 'MM/dd/yyyy HH:mm:ss');
        */
        /*
         * echo Yii::$app->formatter->asDatetime(time());
        echo Yii::$app->formatter->asDatetime(time(), 'MMM d, y HH:mm');
        */


        $yearNumber = date('Y', time());
        return $this->render('//site/index',
            compact('todayHistories',
                'yesterdayHistories',
                'yearStacks',
                'maxDate',
                'minDate',
                'freight',
                'cost',
                'spot',
                'numismatic',
                'last4YearStacks',
                'yearNumber',
                'yearSeparateStacks',
                'weightStacks',
                'countStacks',
                'recentLogs'
            )
        );
    }
    public function actionAlert()
    {
        return $this->render('//site/alert');
    }

    public function actionLogin()
    {
        $this->layout='login';
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login())
        {
            Yii::$app->authManager->revokeAll(Yii::$app->user->identity->id);
            Yii::$app->authManager->assign(Yii::$app->authManager->getRole(Yii::$app->user->identity->role) , Yii::$app->user->identity->id);
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        /*
        $model->name=123;
        $model->email='qwe@mail.ru';
        $model->subject=123123;
        $model->body='asdasdasd';
        Yii::$app->UM->send($model->subject, $model->body,
            Yii::$app->params['adminEmail'], Yii::$app->name,
            $model->email, $model->name);
        */
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

}
