<?php

namespace weebz\yii2basics\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\models\Params;

/**
 * ParamsSearch represents the model behind the search form of `weebz\yii2basics\models\Params`.
 */
class ParamsSearch extends Params
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['description', 'meta_viewport', 'meta_author', 'meta_robots', 'meta_googlebot', 'meta_keywords', 'meta_description', 'canonical', 'host', 'title', 'bussiness_name', 'email', 'fone', 'address', 'postal_code', 'created_at', 'updated_at'], 'safe'],
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
        $query = Configuration::find();

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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'meta_viewport', $this->meta_viewport])
            ->andFilterWhere(['like', 'meta_author', $this->meta_author])
            ->andFilterWhere(['like', 'meta_robots', $this->meta_robots])
            ->andFilterWhere(['like', 'meta_googlebot', $this->meta_googlebot])
            ->andFilterWhere(['like', 'meta_keywords', $this->meta_keywords])
            ->andFilterWhere(['like', 'meta_description', $this->meta_description])
            ->andFilterWhere(['like', 'canonical', $this->canonical])
            ->andFilterWhere(['like', 'host', $this->host])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'bussiness_name', $this->bussiness_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'fone', $this->fone])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'postal_code', $this->postal_code]);
        
        if(isset($this->created_at) && !empty($this->created_at)){ 
            $query->andFilterWhere(['>=', 'created_at', $this->created_at. ' 00:00:00']);
        }
        
        if(isset($this->updated_at) && !empty($this->updated_at)) {
            $query->andFilterWhere(['<=', 'updated_at', $this->updated_at. ' 23:59:59']);
        }
        
        return $dataProvider;
    }
}
