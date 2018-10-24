<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 1/24/15
 * Time: 6:41 PM
 */

namespace app\models\query;

use yii\db\ActiveQuery;
use Yii;

class HistoryQuery extends  ActiveQuery{

    public function today()
    {
        $this->andWhere(["DATE_FORMAT(metalValueDate, '%Y-%m-%d')"=>date('Y-m-d'),]);
        $this->orderBy("metalValueDate DESC");
        return $this;
    }
    public function day($date)
    {
        if($date == date('Y-m-d'))
            return $this->today();
        $this->andWhere(["DATE_FORMAT(metalValueDate, '%Y-%m-%d')"=>$date,]);
        $this->select(["ROUND( AVG(metalValue), 4)  AS metalValue", 'ID', 'metalValueDate', 'metalSymbol']);
        //$this->select("AVG(metalValue) AS avgValue");
        $this->groupBy(["DATE_FORMAT(metalValueDate, '%Y-%m-%d')", "metalSymbol"] );
        return $this;
    }
    public function near($date)
    {
        $this->select( ['*', 'metalValueDate AS nearDate']);
        $this->orderBy("ABS( TO_SECONDS('$date') - TO_SECONDS(metalValueDate) ), metalValueDate DESC");
        return $this;
    }
    public function metal($metal)
    {
        if(is_numeric($metal))
            $this->andWhere(["metalSymbol"=>$metal,]);
        return $this;
    }
} 