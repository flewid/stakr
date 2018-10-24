<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\MetalMint;
use app\models\User;

/**
 * MetalMintSearch represents the model behind the search form about `app\models\MetalMint`.
 */
class MetalMintSearch extends MetalMint
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['title'], 'safe'],
            ['author_id', 'number'],
            ['default', 'boolean'],
            [['country_id'], 'number'],
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
        $query = MetalMint::find();

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
            'id' => $this->id,
            'country_id' => $this->country_id,
            'default' => $this->default,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
