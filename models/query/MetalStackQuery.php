<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 1/24/15
 * Time: 6:41 PM
 */

namespace app\models\query;

use app\models\MetalStack;
use yii\db\ActiveQuery;
use Yii;

class MetalStackQuery extends  ActiveQuery{

    public function mine($strict=false)
    {
        if($strict==true)
            return $this->andWhere(['metalstack.author_id'=>Yii::$app->user->id]);
        if(Yii::$app->user->can('adminPermission'))
            return $this;
        return $this->andWhere(['metalstack.author_id'=>Yii::$app->user->id]);
    }
    public function notInRoll()
    {
        $this->andWhere("roll_id=0 OR roll_id IS NULL");//не важно roll=1 или roll=0
        return $this;
    }
    public function bigRolls()
    {
        $this->andWhere("roll=1");
        return $this;
    }
    public function open()
    {
        $this->andWhere("status=0 OR status IS NULL");
        // this is wrong $this->andWhere(['NOT IN', 'status', [MetalStack::STATUS_LOST, MetalStack::STATUS_SOLD, MetalStack::STATUS_TRADE]]);
        return $this;
    }
    public function lost()
    {
        $this->andWhere(['=', 'status', MetalStack::STATUS_LOST]);
        return $this;
    }
    public function trade()
    {
        $this->andWhere(['=', 'status', MetalStack::STATUS_TRADE]);
        return $this;
    }
    public function notTrade()
    {
        $this->andWhere(['!=', 'status', MetalStack::STATUS_TRADE]);
        return $this;
    }
    public function sold()
    {
        $this->andWhere(['=', 'status', MetalStack::STATUS_SOLD]);
        return $this;
    }
    public function groupByYear()
    {
        $this->select([
            "DATE_FORMAT(metalPurchaseDate, '%Y') AS year",
            'ROUND(SUM(total_numismatic_value),4) AS sumNumismaticPrice',
            'ROUND(SUM(metalPurchasePrice),4) AS sumTotalPrice',
            'ROUND(SUM(totalSpotPrice),4) AS sumSpotPrice'
        ]);
        $this->groupBy('year')->indexBy('year');
        return $this;
    }
    public function groupByYearSeparate()
    {
        $this->select([
            "metalSymbol",
            "DATE_FORMAT(metalPurchaseDate, '%Y') AS year",
            'ROUND(SUM(total_numismatic_value),4) AS sumNumismaticPrice',
            'ROUND(SUM(metalPurchasePrice),4) AS sumTotalPrice',
            'ROUND(SUM(totalSpotPrice),4) AS sumSpotPrice'
        ]);
        $this->groupBy(['year','metalSymbol']);
        return $this;
    }
    public function groupBySymbol()
    {
        $this->select([
            "metalSymbol",
            'ROUND(SUM(weight),4) AS weight',
        ]);
        $this->groupBy(['metalSymbol']);
        return $this;
    }
    public function groupByWeek()
    {
        $this->select([
            "DATE_FORMAT(metalPurchaseDate, '%v') AS week",
            "DATE_FORMAT(metalPurchaseDate, '%Y') AS year",
            'ROUND(SUM(total_numismatic_value),4) AS sumNumismaticPrice',
            'ROUND(SUM(metalPurchasePrice),4) AS sumTotalPrice',
            'ROUND(SUM(totalSpotPrice),4) AS sumSpotPrice'
        ]);
        $this->orderBy('metalPurchaseDate')->groupBy('week')->indexBy('week');
        //$last2MonthDate = date('Y-m-d', time()-24*3600*31*2);
        $last2MonthDate = date('Y-m-d', strtotime('-8 week'));
        $this->andWhere("metalPurchaseDate>='$last2MonthDate'");
        return $this;
    }

    public function groupByLast4Year()
    {
        $this->select([
            "DATE_FORMAT(metalPurchaseDate, '%Y') AS year",
            'ROUND(SUM(total_numismatic_value),4) AS sumNumismaticPrice',
            'ROUND(SUM(metalPurchasePrice),4) AS sumTotalPrice',
            'ROUND(SUM(totalSpotPrice),4) AS sumSpotPrice'
        ]);
        $this->orderBy('metalPurchaseDate')->groupBy('year')->indexBy('year');
        $last4YearDate = date('Y')-4;
        $last4YearDate.="-01-01";
        $currentDate = date('Y-m-d');
        $this->andWhere("metalPurchaseDate>='$last4YearDate' AND metalPurchaseDate<='$currentDate'");
        return $this;
    }
} 