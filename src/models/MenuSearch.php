<?php

namespace weebz\yii2basics\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\models\Menu;

/**
 * MenuSearch represents the model behind the search form of `weebz\yii2basics\models\Menu`.
 */
class MenuSearch extends Menu
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'menu_id', 'status'], 'integer'],
            [['label', 'icon', 'visible', 'url', 'active','icon_style'], 'safe'],
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
    public function search($params,$id = null)
    {
        $query = Menu::find();

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

        $query->andFilterWhere(['like', 'label', $this->label])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'icon_style', $this->icon_style])
            ->andFilterWhere(['like', 'visible', $this->visible])
            ->andFilterWhere(['like', 'active', $this->active]);
        if($id === null && (!isset($this->label) || empty($this->label))){
            $query->andWhere(['menu_id'=>$id]);
            //$query->andWhere(['or', ['=', 'url', '#'],['menu_id'=>$id]]);
        }else{
            $query->andFilterWhere(['menu_id'=>$id]);
        }
        $query->orderBy(['order' => SORT_ASC]);
        
        return $dataProvider;
    }
}
