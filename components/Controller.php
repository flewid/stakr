<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 2/6/15
 * Time: 12:20 PM
 */

namespace app\components;

use app\models\History;
use app\models\MetalType;
use yii\base\Exception;
use yii\helpers\Html;
use yii\web\Controller AS WebController;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\base\InlineAction;

class Controller extends WebController{

    protected function performAjaxValidation($model)
    {
        if(Yii::$app->request->isAjax)
        {
            if($model->load(Yii::$app->request->post()))
            {
                /*
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
                */
                echo json_encode(ActiveForm::validate($model));
                Yii::$app->end();
            }
        }
    }
    public $enableCsrfValidation = false;

    function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
    public function initHistory($exception=false)
    {
        return ;
        $todayHistory = History::find()->today()->one();
        if(!$todayHistory || $todayHistory->metalValueDate<date('Y-m-d H:i:s', time()-(3600*4) ))
        {
            $metalPrices=[];
            $xml = simplexml_load_file("http://www.xmlcharts.com/cache/precious-metals.php");
            if(!$xml->currency && $exception)
                throw new Exception("Xml of metal prices is wrong");
            if($xml->currency)
            {
                foreach ($xml->currency[11]->price as $price)
                    $metalPrices[(string) $price->attributes()->access ]=(float)$price;

                /*$metalPrices = [
                    'gold'=>36.59719,
                    'palladium'=>19.5798,
                    'platinum'=>31.57203,
                    'silver'=>0.4895,
                ];*/
                $metalTypes = MetalType::find()->select(['*', 'LOWER(metalDescription) AS metalDescription'])->indexBy('metalDescription')->all();
                foreach ($metalPrices as $metalDescription=>$value)
                    if(isset($metalTypes[$metalDescription]) && ($metalTypeModel = $metalTypes[$metalDescription]))
                    {
                        $history = new History();
                        $history->metalValueDate = date('Y-m-d H:i:s');
                        $history->metalValue=$value*31.1034768;
                        $history->metalSymbol = "{$metalTypeModel->ID}";
                        if(!$history->save() && $exception)
                            throw new Exception(strip_tags(Html::errorSummary($history, ['header'=>false,])));
                    }
            }
        }
    }
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
      {
          $result = parent::afterAction($action, $result);

          return $result;
      }

    public function goAlert()
    {
        return Yii::$app->getResponse()->redirect(['site/alert']);
    }

    public function access($controller,$action)
    {
        //Yii::$app->user->can('permission');
        //Yii::$app->authManager->checkAccess(Yii::$app->user->id, 'permission');
        if($this->id==$controller)
            $controller = $this;
        else
            $controller = Yii::$app->createControllerByID($controller);
        $action = new InlineAction($action, $controller, 'action'.ucfirst($action));
        if(isset($controller->behaviors['access']) && isset($controller->behaviors['access']->rules))
            foreach ($controller->behaviors['access']->rules as $rule)
            {
                if($rule->allows($action, Yii::$app->user, Yii::$app->request))
                    return true;
            }
        return false;
    }
} 