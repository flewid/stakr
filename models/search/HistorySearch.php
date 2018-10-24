<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\History;

/**
 * HistorySearch represents the model behind the search form about `app\models\History`.
 */
class HistorySearch extends History
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'metalValue'], 'integer'],
            [['metalValueDate', 'metalSymbol'], 'safe'],
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
        $query = History::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query//,
            //'pagination' => [
            //    'pageSize' => 20,
            //    'pageParam' => 'disactive',
            //]
        ]);
        $query->with('type');

        if(!isset($_GET['sort']))
            $query->orderBy('metalValueDate DESC');
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'ID' => $this->ID,
            'metalValueDate' => $this->metalValueDate,
            'metalValue' => $this->metalValue,
        ]);

        $query->andFilterWhere(['like', 'metalSymbol', $this->metalSymbol]);

        return $dataProvider;
    }
}
