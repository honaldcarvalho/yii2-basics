<?php

namespace weebz\yii2basics\models;

use weebz\yii2basics\controllers\AuthController;
use Yii;
use weebz\yii2basics\controllers\ControllerCommon;
use yii\data\ActiveDataProvider;

class ModelCommon extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    const SCENARIO_SEARCH = 'search';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($this->getAttributes() as $key => $value) {
            $scenarios[self::SCENARIO_DEFAULT][] = $key;
            $scenarios[self::SCENARIO_SEARCH][] = $key;
        }
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public static function getClass()
    {   
        $array = explode('\\', get_called_class());
        return end($array);
    }

    public static function getClassPath()
    {
         return get_called_class();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$options = ['orderBy'=>['id' => SORT_DESC]],)
    {

        $className = self::getClass();
        $table = static::tableName();

        $query = static::find();
        
        if(isset($options['select'])) {
            $query->select($options['select']);
        }

        if(isset($options['orderBy'])) {
            $query->orderBy($options['orderBy']);
        }

        if(isset($options['join'])) {
            if(is_array($options['join'])){
                foreach($options['join'] as $model){
                    [$method,$table,$criteria] = $model;
                    $query->join($method,$table,$criteria);
                }
            }
        }

        if(isset($options['groupModel'])){
            $field = ControllerCommon::addSlashUpperLower($className);
            $query->leftJoin($options['groupModel']['table'], "{$table}.{$options['groupModel']['field']} = {$options['groupModel']['table']}.id");
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        
        // grid filtering conditions
        if($this->verGroup) {
            $group_ids = AuthController::User()->getUserGroupsId();
            if(isset($options['groupModel'])){
                $query->andFilterWhere(['in', "{$options['groupModel']['table']}.group_id", $group_ids]);
            }else{
                $table = static::tableName();
                $query->andFilterWhere(['in', "{$table}.group_id", $group_ids]);
            }
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        //create criteria by search type
        foreach ($params as $field => $search) {
            
            if($field == 'page')
                continue;

            $field_type = gettype($search);
            $field_parts = explode(':',$field);
            if(count($field_parts) > 1){
                [$field,$field_type] = $field_parts;
            }

            foreach ($params["{$className}"] as $field => $search) {

                $field_type = gettype($search);
                $field_parts = explode(':',$field);
    
                if(count($field_parts) > 1){
                    [$field,$field_type] = $field_parts;
                }
                if($field_type == 'custom'){
                    $query->andFilterWhere(["$table.$field", $search[0], $search[1]]);
                } else if($field_type == 'between'){
                    $query->andFilterWhere(['between', "$table.$field", $search[0], $search[1]]);
                } else if($field_type == 'string'){

                    if(str_contains($field,'sod') || str_contains($field,'eod')){
                        [$field_date,$pos] = explode('FDT',$field);
                        if($pos == 'sod'){
                            $query->andFilterWhere(['>=', "$table.$field_date", $search]);
                        }else if($pos == 'eod'){
                            $query->andFilterWhere(['<=', "$table.$field_date", $search]);
                        }
                    } else {
                        $query->andFilterWhere(['like', "$table.$field", $search]);
                    }

                } else {
                    $query->andFilterWhere(["$table.$field" => $search]);
                }
            }
        }
        return $dataProvider;
    }

}