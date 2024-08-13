<?php

namespace weebz\yii2basics\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\modules\common\models\LicenseType;

/**
 * LicenseTypeSearch represents the model behind the search form of `weebz\yii2basics\modules\common\models\LicenseType`.
 */
class LicenseTypeSearch extends LicenseType
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'max_devices', 'status'], 'integer'],
            [['name', 'description', 'value', 'contract'], 'safe'],
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
        $query = LicenseType::find();

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
            'max_devices' => $this->max_devices,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'contract', $this->contract]);

        return $dataProvider;
    }
}
