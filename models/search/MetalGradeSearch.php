<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\MetalGrade;
use app\models\User;

/**
 * MetalGradeSearch represents the model behind the search form about `app\models\MetalGrade`.
 */
class MetalGradeSearch extends MetalGrade
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID'], 'integer'],
            ['author_id', 'number'],
            ['default', 'boolean'],
            [['metalGrade', 'metalGradeDescription', 'metalGradeScale'], 'safe'],
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
        $query = MetalGrade::find();

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

        $query->andFilterWhere(['like', 'metalGrade', $this->metalGrade])
            ->andFilterWhere(['like', 'metalGradeDescription', $this->metalGradeDescription])
            ->andFilterWhere(['like', 'metalGradeScale', $this->metalGradeScale]);

        return $dataProvider;
    }
}
