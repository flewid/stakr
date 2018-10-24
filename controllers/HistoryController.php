<?php

namespace app\controllers;

use app\models\Log;
use app\models\MetalType;
use app\models\Odesk;
use Yii;
use app\models\History;
use app\models\search\HistorySearch;
use yii\filters\AccessControl;
use app\components\Controller;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Goutte\Client;

/**
 * HistoryController implements the CRUD actions for History model.
 */
class HistoryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['cron', 'list', 'import', 'cron2', 'cron3'],
                        'allow' => true,
                    ],
                    [
                        'actions' => [  'view','index',],
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
     * Lists all History models.
     * @return mixed
     */
    public function actionList($metal_id, $date)
    {
        $this->initHistory();
        $model = History::find()->metal($metal_id)->day($date)->one();
        if(!$model)
            $model = History::find()->metal($metal_id)->near($date)->one();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model->getAttributes(['ID', 'metalValueDate', 'metalSymbol', 'metalValue', 'nearDate']);
    }
    public function actionImport()
    {
        /*$items = [
            '2000'=>'279.11',
            '2001'=>'271.04',
            '2002'=>'309.73',
            '2003'=>'363.38',
            '2004'=>'409.72',
            '2005'=>'444.74',
            '2006'=>'603.46',
            '2007'=>'695.39',
            '2008'=>'871.96',
            '2009'=>'972.35',
            '2010'=>'1224.35',
            '2011'=>'1571.52',
            '2012'=>'1668.98',
            '2013'=>'1411.23',
            '2014'=>'1266.40',
        ];
        $symbolID = MetalType::find()->where(['metalDescription'=>'gold',])->one()->ID;
        foreach ($items as $year=>$value) {
            $history = new History();
            $history->metalSymbol = $symbolID;
            $history->metalValueDate = $year.'-01-01';
            $history->metalValue = $value;
            $history->save();
            echo Html::errorSummary($history);
        }*/
        /*$metal = 'palladium';
        $symbolID = MetalType::find()->where(['metalDescription'=>$metal,])->one()->ID;
        $content = file("$metal.html");
        foreach ($content as $line) {
            $line = explode(' ', $line);
            $history = new History();
            $history->metalSymbol = $symbolID;
            $history->metalValueDate = trim($line[0]).'-01-01';
            $history->metalValue = trim($line[1]);
            $history->save();
            echo Html::errorSummary($history);
        }*/
    }
    public function actionCron2()
    {
        //'D, d M Y H:i:s O'
        $xml = simplexml_load_file("https://www.upwork.com/find-work-home/rss/?topic=1811945&securityToken=b2cd5fd6f1c9f734f308f531342368da49f2882940bee1d2c1aefe93756da3cb~da59747");


        $bodies=[];
        foreach ($xml->channel->item as $item)
        {
            $title = (string) $item->title;
            if(strpos($title, 'Upwork RSS')!==false)
                continue;
            
            $pubDate = \DateTime::createFromFormat(\DateTime::RSS, (string) $item->pubDate)->format('Y-m-d H:i:s');
            $odesk = Odesk::find()->where(['pubDate'=>$pubDate])->one();
            if(!$odesk)
            {
                $odesk = new Odesk();
                $odesk->date = date('Y-m-d H:i:s');
                $odesk->pubDate = $pubDate;
                $odesk->title = Yii::$app->formatter->asTextLimit((string) $item->title, 50);
                $odesk->link = (string) $item->link;
                $odesk->description = (string) $item->description;
                if($odesk->save())
                {
                    $body = "<h1>$odesk->title</h1>";
                    $body.= Html::errorSummary($odesk, ['header'=>false,]);
                    //$body.= $odesk->description;
                    $body.= '<br>';
                    $bodies[]=$body;
                }else
                    Yii::$app->UM->send('Yii projects from odesk',
                        Html::errorSummary($odesk).'=>>>>'.$item->title,
                        'nurbek.nurjanov@mail.ru',
                        'Нурбек Нуржанов',
                        'no-replay@odesk.com',
                        'Odesk',
                        null
                    );
            }
        }
        if($bodies)
            Yii::$app->UM->send('Yii projects from odesk',
                implode('<br><br><br>',$bodies).'<br> from '.@$_GET['from'],
                'nurbek.nurjanov@mail.ru',
                'Нурбек Нуржанов',
                'no-replay1@odesk.com',
                'Odesk',
                null
            );
        echo 'cron2';
    }

    public function actionCron()
    {
        $this->initHistory(true);
        Log::create(['description'=>"Cron successfully worked at ".Yii::$app->formatter->asDatetime(time())]);
    }

    public function actionCron3(){
        $client = new Client();
        $crawler = $client->request('GET', 'https://goldprice.com/');

        $gold=$crawler->filter('.nfprice')->eq(0)->text();
        $gold=str_replace(array("$"," "),array("",""),$gold);
        $gold=str_replace(",","",$gold);
        $modelGold = new History();
        $modelGold->metalSymbol = 5;
        $modelGold->metalValueDate = date("Y-m-d H:i:s");
        $modelGold->metalValue = $gold;
        $modelGold->save();

        $silver=$crawler->filter('.nfprice')->eq(1)->text();
        $silver=str_replace(array("$"," "),array("",""),$silver);
        $silver=str_replace(array(","," "),array("",""),$silver);
        $modelSilver = new History();
        $modelSilver->metalSymbol = 8;
        $modelSilver->metalValueDate = date("Y-m-d H:i:s");
        $modelSilver->metalValue = $silver;
        $modelSilver->save();

        $platinum=$crawler->filter('.nfprice')->eq(2)->text();
        $platinum=str_replace(array("$"," "),array("",""),$platinum);
        $platinum=str_replace(array(","," "),array("",""),$platinum);
        $modelPlatinum = new History();
        $modelPlatinum->metalSymbol = 7;
        $modelPlatinum->metalValueDate = date("Y-m-d H:i:s");
        $modelPlatinum->metalValue = $platinum;
        $modelPlatinum->save();

        $palladium=$crawler->filter('.nfprice')->eq(3)->text();
        $palladium=str_replace(array("$"," "),array("",""),$palladium);
        $palladium=str_replace(array(","," "),array("",""),$palladium);
        $modelPalladium = new History();
        $modelPalladium->metalSymbol = 6;
        $modelPalladium->metalValueDate = date("Y-m-d H:i:s");
        $modelPalladium->metalValue = $palladium;
        $modelPalladium->save();

        echo "<br>Gold = ".$gold;
        echo "<br>silver = ".$silver;
        echo "<br>platinum = ".$platinum;
        echo "<br>palladium = ".$palladium;

        //$this->initHistory(true);
        Log::create(['description'=>"Cron successfully worked at ".Yii::$app->formatter->asDatetime(time())]);
    }

    public function actionIndex()
    {
        $this->initHistory();
        $searchModel = new HistorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single History model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Deletes an existing History model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the History model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return History the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = History::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
