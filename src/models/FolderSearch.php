<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\controllers\ControllerCommon;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\models\Folder;

/**
 * FolderSearch represents the model behind the search form of `weebz\yii2basics\models\Folder`.
 */
class FolderSearch extends Folder
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'external', 'status'], 'integer'],
            [['name', 'description', 'created_at', 'updated_at'], 'safe'],
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
        $query = Folder::find();

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
            'external' => $this->external,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andWhere(['or',['in','group_id',ControllerCommon::getUserGroups()],['group_id'=>null], ['group_id'=>1]]);

        if(isset($this->created_at) && !empty($this->created_at)){ 
            $query->andFilterWhere(['>=', 'created_at', $this->created_at. ' 00:00:00']);
        }
        
        if(isset($this->updated_at) && !empty($this->updated_at)) {
            $query->andFilterWhere(['<=', 'updated_at', $this->updated_at. ' 23:59:59']);
        }
        
        return $dataProvider;
    }
}
