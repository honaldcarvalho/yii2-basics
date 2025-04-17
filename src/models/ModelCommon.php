<?php

namespace weebz\yii2basics\models;

use yii\data\ActiveDataProvider;
use weebz\yii2basics\controllers\AuthController;

class ModelCommon extends \yii\db\ActiveRecord
{
    public $verGroup = false;
    const SCENARIO_STATUS = 'status';
    const SCENARIO_SEARCH = 'search';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($this->getAttributes() as $key => $value) {
            $scenarios[self::SCENARIO_DEFAULT][] = $key;
            $scenarios[self::SCENARIO_SEARCH][] = $key;
        }
        $scenarios[self::SCENARIO_STATUS][] = 'status';

        return $scenarios;
    }

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
    
        if ($this->hasAttribute('group_id')) {
            $group = \Yii::getAlias('@main-group', false);
            if (!$group) {
                $this->group_id = AuthController::userGroup();
            }
        }
    
        return true;
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
    public function search($params,$options = ['pageSize'=>10, 'orderBy'=>['id' => SORT_DESC],'order'=>false],)
    {
        $this->scenario = self::SCENARIO_SEARCH;

        $className = self::getClass();
        $table = static::tableName();
        $pageSize = 10;
        $order = false;
        $orderField = false;

        $query = static::find();
        
        if(isset($options['select'])) {
            $query->select($options['select']);
        }

        if(isset($options['orderBy'])) {
            $query->orderBy($options['orderBy']);
        }

        if(isset($options['pageSize'])) {
            $pageSize = $options['pageSize'];
        }

        /**
            AQUI FAZ A VERIFICAÇÃO SE TEM UM ITEM DE ORDENAMENTO QUE MUDA A TAMANHO DA LISTAGEM. CASO SEJA FORNECIDO UM CAMPO FLAG E ELE NÃO SEJA NULO/VAZIO
            O TAMANHO PASSA PARA 10000
        */
        if(isset($options['order']) && $options['order'] && !empty($options['order']) && count($params) > 0) {
            $query->orderBy([$options['order']['field'] => SORT_ASC]);

            if(
                    (
                        isset($options['order']['flag'] ) && 
                        $options['order']['flag'] != false && 
                        isset($params[$className][$options['order']['flag']]) && 
                        !empty($params[$className][$options['order']['flag']])
                    )
                )
            {
                foreach ($params["{$className}"] as $field => $search) {
                    if(!empty($search)){
                        $pageSize = 10000;
                        break;
                    }
                }
            }
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
            $field =  AuthController::addSlashUpperLower($className);
            $query->leftJoin($options['groupModel']['table'], "{$table}.{$options['groupModel']['field']} = {$options['groupModel']['table']}.id");
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize
            ],
        ]);

        $this->load($params);
        
        // grid filtering conditions
        $user = AuthController::User();
        if($this->verGroup && $user) {
            $group_ids = $user->getUserGroupsId();
            $group_ids[] = 1;
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

            if(!isset($params["{$className}"]))
                continue;
            
            foreach ($params["{$className}"] as $field => $search) {

                $field_type = gettype($search);
                if (is_numeric($search) && (int)$search == $search) {
                    $field_type = "number";
                } 
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
        // $query = $dataProvider->query;
        // dd($query->createCommand()->getRawSql());
        return $dataProvider;
    }

    public static function clearFrontendCache($key)
    {
        // Envia uma solicitação para o frontend limpar o cache
        $url = \Yii::getAliase("@host");
        $frontendUrl = "{$url}/site/clear-cache?key={$key}";

        $ch = curl_init($frontendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $response = curl_exec($ch);
        curl_close($ch);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->clearCache();
        
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->clearCache();
    }

    protected function clearCache()
    {
        // Gera a chave de cache automaticamente com base no nome do modelo
        $cacheKey = 'cache_' . strtolower((new \ReflectionClass($this))->getShortName());

        \Yii::$app->cache->delete($cacheKey);
    }

    /**
     * Limpa uma chave de cache específica.
     * @param string $cacheKey Nome da chave do cache.
     */
    public static function clearCacheCustom($cacheKey)
    {
        \Yii::$app->cache->delete($cacheKey);
        return true;
    }

    /**
     * Limpa múltiplas chaves de cache específicas.
     * @param array $cacheKeys Lista de chaves para serem apagadas.
     */
    public static function clearMultipleCaches(array $cacheKeys)
    {
        foreach ($cacheKeys as $cacheKey) {
           \Yii::$app->cache->delete($cacheKey);
        }
        return true;
    }

}