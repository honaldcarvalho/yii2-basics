<?php

namespace weebz\yii2basics\modules\common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\modules\common\models\Rule;

/**
 * RuleSearch represents the model behind the search form of `weebz\yii2basics\modules\common\models\Rule`.
 */
class RuleSearch extends Rule
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'group_id', 'status'], 'integer'],
            [['controller', 'actions','origin'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Rule::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'group_id' => $this->group_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'controller', $this->controller])
            ->andFilterWhere(['like', 'origin', $this->origin])
            ->andFilterWhere(['like', 'actions', $this->actions]);

        return $dataProvider;
    }
}
