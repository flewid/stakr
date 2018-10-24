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

class SoldForm extends Model{

    public $stack_id;
    public $sold_price;

    public function rules()
    {
        return [
            [['stack_id', 'sold_price'], 'required',],
            [['sold_price'], 'number',],
        ];
    }
    public function attributeLabels()
    {
        return [
            'stack_id'=>'Stack',
            'sold_price'=>'Sold price',
        ];
    }

}