<?php

namespace app\models\search;

use app\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\MetalStack;
//use yii\web\User;

/**
 * MetalStackSearch represents the model behind the search form about `app\models\MetalStack`.
 */
class MetalStackSearch extends MetalStack
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['roll'], 'boolean'],
            [['ID', 'author_id', 'metalPurchasePrice', 'metalShippingCost', 'currentPrice', 'metalQuantity', 'metalTotalPaid', 'roll_id'], 'integer'],
            [['metalOriginCountry', 'metalSymbol', 'metalVendor', 'metalPurchaseDate', 'metalDescription', 'metalShape', 'spotPrice', 'metalOriginMint', 'metalGrade', 'author_id', 'title'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $width=['type', 'vendor', 'shape', 'grade'];
        if(Yii::$app->user->identity->role==User::ROLE_ADMIN)
            $width[]='author';

        $query = MetalStack::find()->with($width);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'ID' => $this->ID,
            'author_id' => $this->author_id,
            'roll' => $this->roll,
            'roll_id' => $this->roll_id,
            'metalPurchaseDate' => $this->metalPurchaseDate,
            'metalPurchasePrice' => $this->metalPurchasePrice,
            'metalShippingCost' => $this->metalShippingCost,
            'spotPrice' => $this->spotPrice,
            //'currentPrice' => $this->currentPrice,
            'metalQuantity' => $this->metalQuantity,
            'metalTotalPaid' => $this->metalTotalPaid,
        ]);

        $query->andFilterWhere(['like', 'metalOriginCountry', $this->metalOriginCountry])
            ->andFilterWhere(['like', 'metalSymbol', $this->metalSymbol])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'metalVendor', $this->metalVendor])
            ->andFilterWhere(['like', 'metalDescription', $this->metalDescription])
            ->andFilterWhere(['like', 'metalShape', $this->metalShape])
            ->andFilterWhere(['like', 'metalOriginMint', $this->metalOriginMint])
            ->andFilterWhere(['like', 'metalGrade', $this->metalGrade]);

        return $dataProvider;
    }

    public function init()
    {

    }
}
