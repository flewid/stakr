<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 9/15/15
 * Time: 12:48 PM
 */

namespace app\models;

use Yii;
use yii\base\Model;

class Form extends Model{

    public $stack_id;
    public $trade_id;
    public $trade_text;
    public $trade_radio;

    public function rules()
    {
        return [
            [['stack_id'], 'required',],
            [['trade_radio'], 'required',],
            [['trade_id'], 'required', 'when'=>function ($model) {
                    return $model->trade_radio == 'stack';
                },],
            [['trade_text'], 'required', 'when'=>function ($model) {
                    return $model->trade_radio == 'other';
                },],
        ];
    }
    public function attributeLabels()
    {
        return [
            'trade_radio'=>'Trade for',
            'trade_text'=>'Traded for non precious metal',
            'trade_id'=>'Trade stack',
        ];
    }

}