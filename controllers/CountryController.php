<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 9/27/15
 * Time: 10:32 AM
 */

namespace app\controllers;


use app\components\Controller;
use app\models\Country;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;

class CountryController extends Controller{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'view','index', 'update', 'delete', 'init'],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['cron', 'list'],
                        'allow' => true,
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

    public function actionInit()
    {
        $countries=Country::find()->where("alpha2code=''")->indexBy('title')->all();
        $content = file_get_contents("https://restcountries.eu/rest/v1/all");
        $json = json_decode($content);

        foreach ($countries as $country) {
            $title = $country->title;
            if(preg_match("/,/", $title))
                $title = explode(',', $title)[0];
            $content = file_get_contents("https://restcountries.eu/rest/v1/name/".$title);
            $json = json_decode($content);
            $country->alpha2code= strtolower($json[0]->alpha2Code);
            if(!$country->save());
            echo Html::errorSummary($country)."---<br>";
        }

        /*$countries=Country::find()->indexBy('title')->all();
        $content = file_get_contents("https://restcountries.eu/rest/v1/all");
        $json = json_decode($content);
        foreach ($json as $apiCountry) {
            if(isset($countries[$apiCountry->name]))
            {
                $countries[$apiCountry->name]->alpha2code = strtolower($apiCountry->alpha2Code);
                $countries[$apiCountry->name]->save();
                echo Html::errorSummary($countries[$apiCountry->name])."---<br>";
            }
        }*/

        /*foreach ($countries as $model) {
            $content = file_get_contents("https://restcountries.eu/rest/v1/name/".$model->title);
            $json = json_decode($content);
            $model->alpha2code = strtolower($json[0]->alpha2Code);
            if(!$model->save());
            echo Html::errorSummary($model)."---<br>";
        }*/

    }

} 