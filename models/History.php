<?php

namespace app\models;

use app\models\query\HistoryQuery;
use Yii;

/**
 * This is the model class for table "metalvaluehistoric".
 *
 * @property integer $ID
 * @property string $metalValueDate
 * @property string $metalSymbol
 * @property integer $metalValue
 */
class History extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'metalvaluehistoric';
    }
    public static function find()
    {
        return new HistoryQuery(get_called_class());
    }

    public function getTitle()
    {
        return $this->type->metalDescription;
    }
    public function getType()
    {
        return $this->hasOne(MetalType::className(), ['ID' => 'metalSymbol']);
    }

    //public $avgValue;
    public $nearDate;
    public $totalValue;
    public $year;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['metalValueDate', 'metalSymbol', 'metalValue'], 'required'],
            [['metalValueDate'], 'safe'],
            [['metalValue'], 'number'],
            //[['metalSymbol'], 'string',],
            [['metalSymbol'], 'number',]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'metalValueDate' => 'Date',
            'metalSymbol' => 'Symbol',
            'metalValue' => 'Spot price',
        ];
    }
}
