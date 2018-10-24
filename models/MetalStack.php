<?php

namespace app\models;

use app\models\query\MetalStackQuery;
use Yii;
use yii\helpers\Html;

//use yii\web\User;

/**
 * This is the model class for table "metalstack".
 *
 * @property integer $ID
 * @property string $metalSymbol
 * @property string $metalVendor
 * @property string $metalPurchaseDate
 * @property integer $metalPurchasePrice
 * @property integer $numismatic_value
 * @property integer $metalShippingCost
 * @property string $metalDescription
 * @property string $metalShape
 * @property string $spotPrice
 * @property integer $currentPrice
 * @property integer $metalQuantity
 * @property integer $metalTotalPaid
 * @property string $metalOriginMint
 * @property string $metalGrade
 * @property string $roll
 * @property string $roll_id
 * @property string $title
 * @property string $status
 * @property string $weight
 * @property string $total_numismatic_value
 * @property string $totalSpotPrice
 */
class MetalStack extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'metalstack';
    }
    const STATUS_SOLD=1;
    const STATUS_TRADE=2;
    const STATUS_LOST=3;
    public $statusValues=[];
    public $afterConstruct;
    public function init()
    {
        $this->statusValues=[
            self::STATUS_SOLD=>'Sold',
            self::STATUS_TRADE=>'Trade',
            self::STATUS_LOST=>'Lost',
        ];
        if($this->afterConstruct)
        {
            $this->author_id=Yii::$app->user->id;
            $this->roll=0;
            $this->weight=1;
            $this->metalQuantity=1;
            $this->metalPurchasePrice=0;
            $this->metalShippingCost=0;
            $this->metalTotalPaid=0;
            $this->spotPrice=0;
            $this->totalSpotPrice=0;
            $this->metalPurchaseDate=date('Y-m-d');
        }
    }
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            $this->metalPurchaseDate = date('Y-m-d',strtotime($this->metalPurchaseDate));
            if($this->isNewRecord)
            {
                $this->author_id = Yii::$app->user->identity->id;
            }
            return true;
        }
        else
            return false;
    }


    public $year;
    public $week;
    public $sumTotalPrice;
    public $sumSpotPrice;
    public $sumNumismaticPrice;
    public $count;

    public $currentPrice=0;

    public function afterFind()
    {
        /*
        if($this->metalSymbol)
            $this->currentPrice = History::find()->today()->metal($this->metalSymbol)->one()->metalValue;
        */
        parent::afterFind();
    }
    public function getStatusText()
    {
        if($this->status)
            return $this->statusValues[$this->status];
    }

    public static function find()
    {
        return new MetalStackQuery(get_called_class());
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'metalShape', 'metalSymbol','metalPurchaseDate','metalPurchasePrice',
                'spotPrice','metalQuantity', 'metalTotalPaid', 'weight'
                ],
                'required'],

            [[ 'metalSymbol', 'metalVendor', 'metalPurchaseDate', 'metalPurchasePrice', 'metalShippingCost', 'metalDescription', 'metalShape', 'spotPrice', 'currentPrice', 'metalQuantity', 'metalTotalPaid', 'metalOriginMint', 'metalGrade'],
                'safe'],
            ['author_id', 'required'],
            ['totalSpotPrice', 'required'],
            ['metalShippingCost', 'required'],
            ['totalSpotPrice', 'number'],
            [['weight'], 'number', 'min'=>0.001],
            [['metalQuantity'], 'number', 'min'=>1],
            //['title', 'required'],
            ['title', 'unique'],
            [['roll'], 'boolean'],
            ['mintMark', 'safe'],
            [['metalPurchasePrice', 'metalShippingCost', 'roll_id'], 'number'],
            [['currentPrice', 'weight', 'total_numismatic_value'], 'number'],
            [['numismatic_value'], 'number'],
            [['spotPrice'], 'safe'],
            [['metalPurchaseDate', 'spotPrice'], 'safe'],
            [['metalQuantity'], 'integer'],
            [['metalTotalPaid'], 'number'],
            [['metalVendor', 'metalShape', 'metalOriginMint', 'metalGrade'], 'string', 'max' => 128],
            [['metalSymbol'], 'string', 'max' => 3],
            [['metalDescription'], 'string', 'max' => 255],
        ];
    }

    public function getLink()
    {
        return Html::a($this->title, ['metal-stack/view', 'id'=>$this->ID]);
    }
    /**
     * @inheritdoc
     */

    public function afterSave($insert, $changedAttributes)
    {
        if(!$this->roll_id)
        {
            if($this->type)
                $this->title=$this->type->metalSymbol.'-'.$this->ID;
        }
        else
            $this->title=$this->parentRoll->title.'-'.$this->ID;
        $this->updateAttributes(['title']);
        parent::afterSave($insert, $changedAttributes);
    }
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    public function getTrade()
    {
        return $this->hasOne(MetalStack::className(), ['ID' => 'trade_id']);
    }
    public function getKids()
    {
        return $this->hasMany(MetalStack::className(), ['roll_id' => 'ID']);
    }
    public function getParentRoll()
    {
        return $this->hasOne(MetalStack::className(), ['ID' => 'roll_id']);
    }

    public function getType()
    {
        return $this->hasOne(MetalType::className(), ['ID' => 'metalSymbol']);
    }
    public function getGrade()
    {
        return $this->hasOne(MetalGrade::className(), ['ID' => 'metalGrade']);
    }
    public function getMint()
    {
        return $this->hasOne(MetalMint::className(), ['id' => 'metalOriginMint']);
    }
    public function getVendor()
    {
        return $this->hasOne(MetalVendors::className(), ['ID' => 'metalVendor']);
    }
    public function getShape()
    {
        return $this->hasOne(MetalShape::className(), ['ID' => 'metalShape']);
    }

    public function attributeLabels()
    {
        return [
            'ID' => Yii::t('app', 'ID'),
            'metalSymbol' => Yii::t('app', 'Symbol'),
            'metalVendor' => Yii::t('app', 'Vendor'),
            'metalPurchaseDate' => Yii::t('app', 'Purchase Date'),
            'metalPurchasePrice' => Yii::t('app', 'Purchase Price'),
            'metalShippingCost' => Yii::t('app', 'Shipping Cost'),
            'metalDescription' => Yii::t('app', 'Description'),
            'metalShape' => Yii::t('app', 'Shape'),
            'spotPrice' => Yii::t('app', 'Spot price'),
            'currentPrice' => Yii::t('app', 'Current Price'),
            'metalQuantity' => Yii::t('app', 'Quantity'),
            'metalTotalPaid' => Yii::t('app', 'Total Paid'),
            'metalOriginMint' => Yii::t('app', 'Origin Mint'),
            'metalGrade' => Yii::t('app', 'Grade'),
            'author_id' => Yii::t('app', 'Author'),
            'mintMark' => Yii::t('app', 'Mint mark'),
            'roll' => Yii::t('app', 'Is roll'),
            'roll_id' => Yii::t('app', 'Parent roll'),
            'numismatic_value' => Yii::t('app', 'Numismatic price'),
            'total_numismatic_value' => Yii::t('app', 'Total numismatic price'),
        ];
    }
}
