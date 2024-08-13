<?php

namespace weebz\yii2basics\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\modules\common\models\EmailService;

/**
 * EmailServiceSearch represents the model behind the search form of `weebz\yii2basics\modules\common\models\EmailService`.
 */
class EmailServiceSearch extends EmailService
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'enable_encryption', 'port'], 'integer'],
            [['description', 'scheme', 'encryption', 'host', 'username', 'password'], 'safe'],
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
        $query = EmailService::find();

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
            'enable_encryption' => $this->enable_encryption,
            'port' => $this->port,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'scheme', $this->scheme])
            ->andFilterWhere(['like', 'encryption', $this->encryption])
            ->andFilterWhere(['like', 'host', $this->host])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password]);

        return $dataProvider;
    }
}
