<?php

namespace weebz\yii2basics\modules\common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\modules\common\models\License;

/**
 * LicenseSearch represents the model behind the search form of `weebz\yii2basics\modules\common\models\License`.
 */
class LicenseSearch extends License
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'license_type_id', 'group_id', 'status'], 'integer'],
            [['validate', 'created_at', 'updated_at'], 'safe'],
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
        $query = License::find();

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
            'license_type_id' => $this->license_type_id,
            'group_id' => $this->group_id,
            'validate' => $this->validate,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }
}
