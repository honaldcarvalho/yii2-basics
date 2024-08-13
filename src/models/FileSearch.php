<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\modules\common\controllers\ControllerCommon;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use weebz\yii2basics\modules\common\models\File;

/**
 * FileSearch represents the model behind the search form of `weebz\yii2basics\modules\common\models\File`.
 */
class FileSearch extends File
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'folder_id', 'size'], 'integer'],
            [['name','type', 'description', 'path', 'url', 'pathThumb', 'urlThumb', 'extension', 'created_at', 'updated_at'], 'safe'],
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
        $controller = new Controller(0,0);
        $query = File::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at' => SORT_DESC]],
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
            'folder_id' => $this->folder_id,
            'size' => $this->size
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'pathThumb', $this->pathThumb])
            ->andFilterWhere(['like', 'urlThumb', $this->urlThumb])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'extension', $this->extension])
            ->andWhere(['or',['in','group_id',$controller::getUserGroups()],['group_id'=>null], ['group_id'=>1]]);

        if(isset($this->created_at) && !empty($this->created_at)){ 
            $query->andFilterWhere(['>=', 'created_at', $this->created_at. ' 00:00:00']);
        }
        
        if(isset($this->updated_at) && !empty($this->updated_at)) {
            $query->andFilterWhere(['<=', 'updated_at', $this->updated_at. ' 23:59:59']);
        }
        
        return $dataProvider;
    }
}
