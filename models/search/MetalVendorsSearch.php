<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\MetalVendors;
use app\models\User;

/**
 * MetalVendorsSearch represents the model behind the search form about `app\models\MetalVendors`.
 */
class MetalVendorsSearch extends MetalVendors
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID'], 'integer'],
            [['vendorName', 'vendorSite', 'vendorCountry'], 'safe'],
            ['author_id', 'number'],
            ['default', 'boolean'],
        ];
    }
    public function init()
    {

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
        $query = MetalVendors::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if(Yii::$app->user->identity->role==User::ROLE_ADMIN)
            $dataProvider->query->with(['author']);
        if(Yii::$app->user->identity->role==User::ROLE_USER)
            $query->andWhere("author_id='".Yii::$app->user->identity->id."' OR `default`='1'");

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'ID' => $this->ID,
            'default' => $this->default,
        ]);

        $query->andFilterWhere(['like', 'vendorName', $this->vendorName])
            ->andFilterWhere(['like', 'vendorSite', $this->vendorSite])
            ->andFilterWhere(['like', 'vendorCountry', $this->vendorCountry]);

        return $dataProvider;
    }
}
