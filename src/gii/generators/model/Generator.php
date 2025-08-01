<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace weebz\yii2basics\gii\generators\model;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    const RELATIONS_NONE = 'none';
    const RELATIONS_ALL = 'all';
    const RELATIONS_ALL_INVERSE = 'all-inverse';
    const JUNCTION_RELATION_VIA_TABLE = 'table';
    const JUNCTION_RELATION_VIA_MODEL = 'model';

    public $db = 'db';
    public $ns = 'app\models';
    /**
     * @var string
     */
    public $tableName = '';
    /**
     * @var string
     */
    public $modelClass = '';
    /**
     * @var string
     */
    public $baseClass = 'yii\db\ActiveRecord';
    public $generateRelations = self::RELATIONS_ALL;
    public $generateJunctionRelationMode = self::JUNCTION_RELATION_VIA_TABLE;
    public $useClassConstant;
    public $generateRelationsFromCurrentSchema = true;
    public $generateRelationNameFromDestinationTable = false;
    public $generateLabelsFromComments = false;
    public $useTablePrefix = false;
    public $standardizeCapitals = false;
    public $singularize = false;
    public $useSchemaName = true;
    public $generateQuery = false;
    public $queryNs = 'app\models';
    /**
     * @var string|null
     */
    public $queryClass;
    /**
     * @var string
     */
    public $queryBaseClass = 'yii\db\ActiveQuery';

    /**
     * @var string[]|null
     */
    protected $tableNames;
    /**
     * @var string[]
     */
    protected $classNames = [];


    public function init()
    {
        parent::init();

        if ($this->useClassConstant === null) {
            $this->useClassConstant = PHP_VERSION_ID >= 50500;
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class for the specified database table.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['db', 'tableName', 'modelClass', 'baseClass', 'queryClass', 'queryBaseClass'], 'trim'],
            [
                ['ns', 'queryNs'],
                'filter',
                'filter' => static function ($value) {
                    return $value === null ? null : trim($value, ' \\');
                }
            ],
            [['db', 'ns', 'tableName', 'baseClass', 'queryNs', 'queryBaseClass'], 'required'],
            [['db', 'modelClass', 'queryClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass', 'queryNs', 'queryBaseClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['tableName'], 'match', 'pattern' => '/^([\w ]+\.)?([\w\* ]+)$/', 'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['ns', 'queryNs'], 'validateNamespace'],
            [['tableName'], 'validateTableName'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            [['baseClass'], 'validateClass', 'params' => ['extends' => ActiveRecord::className()]],
            [['queryBaseClass'], 'validateClass', 'params' => ['extends' => ActiveQuery::className()]],
            [['generateRelations'], 'in', 'range' => [self::RELATIONS_NONE, self::RELATIONS_ALL, self::RELATIONS_ALL_INVERSE]],
            [['generateJunctionRelationMode'], 'in', 'range' => [self::JUNCTION_RELATION_VIA_TABLE, self::JUNCTION_RELATION_VIA_MODEL]],
            [
                ['generateLabelsFromComments', 'useTablePrefix', 'useSchemaName', 'generateQuery', 'generateRelationsFromCurrentSchema', 'generateRelationNameFromDestinationTable', 'useClassConstant', 'enableI18N', 'standardizeCapitals', 'singularize'],
                'boolean'
            ],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ns' => 'Namespace',
            'db' => 'Database Connection ID',
            'tableName' => 'Table Name',
            'standardizeCapitals' => 'Standardize Capitals',
            'singularize' => 'Singularize',
            'modelClass' => 'Model Class Name',
            'baseClass' => 'Base Class',
            'generateRelations' => 'Generate Relations',
            'generateJunctionRelationMode' => 'Generate Junction Relations As',
            'generateRelationsFromCurrentSchema' => 'Generate Relations from Current Schema',
            'generateRelationNameFromDestinationTable' => 'Generate Relation Names Using Target Table Name',
            'useClassConstant' => 'Use `::class`',
            'generateLabelsFromComments' => 'Generate Labels from DB Comments',
            'generateQuery' => 'Generate ActiveQuery',
            'queryNs' => 'ActiveQuery Namespace',
            'queryClass' => 'ActiveQuery Class',
            'queryBaseClass' => 'ActiveQuery Base Class',
            'useSchemaName' => 'Use Schema Name',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'ns' => 'This is the namespace of the ActiveRecord class to be generated, e.g., <code>app\models</code>',
            'db' => 'This is the ID of the DB application component.',
            'tableName' => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>post</code>.
                The table name may consist of the DB schema part if needed, e.g. <code>public.post</code>.
                The table name may end with asterisk to match multiple table names, e.g. <code>tbl_*</code>
                will match tables who name starts with <code>tbl_</code>. In this case, multiple ActiveRecord classes
                will be generated, one for each matching table name; and the class names will be generated from
                the matching characters. For example, table <code>tbl_post</code> will generate <code>Post</code>
                class.',
            'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
                the namespace part as it is specified in "Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveRecord classes will be generated.',
            'standardizeCapitals' => 'This indicates whether the generated class names should have standardized capitals. For example,
            table names like <code>SOME_TABLE</code> or <code>Other_Table</code> will have class names <code>SomeTable</code>
            and <code>OtherTable</code>, respectively. If not checked, the same tables will have class names <code>SOMETABLE</code>
            and <code>OtherTable</code> instead.',
            'singularize' => 'This indicates whether the generated class names should be singularized. For example,
            table names like <code>some_tables</code> will have class names <code>SomeTable</code>.',
            'baseClass' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
            'generateRelations' => 'This indicates whether the generator should generate relations based on
                foreign key constraints it detects in the database. Note that if your database contains too many tables,
                you may want to uncheck this option to accelerate the code generation process.',
            'generateJunctionRelationMode' => 'This indicates whether junction relations are generated with `viaTable()` or `via()` (Via Model) relations.
                Make sure you also generate the junction models when using the "Via Model" option.
            ',
            'generateRelationsFromCurrentSchema' => 'This indicates whether the generator should generate relations from current schema or from all available schemas.',
            'generateRelationNameFromDestinationTable' => 'This indicates whether the relation names should use target table name.',
            'useClassConstant' => 'Use the `::class` constant instead of the `::className()` method.',
            'generateLabelsFromComments' => 'This indicates whether the generator should generate attribute labels
                by using the comments of the corresponding DB columns.',
            'useTablePrefix' => 'This indicates whether the table name returned by the generated ActiveRecord class
                should consider the <code>tablePrefix</code> setting of the DB connection. For example, if the
                table name is <code>tbl_post</code> and <code>tablePrefix=tbl_</code>, the ActiveRecord class
                will return the table name as <code>{{%post}}</code>.',
            'useSchemaName' => 'This indicates whether to include the schema name in the ActiveRecord class
                when it\'s auto generated. Only non default schema would be used.',
            'generateQuery' => 'This indicates whether to generate ActiveQuery for the ActiveRecord class.',
            'queryNs' => 'This is the namespace of the ActiveQuery class to be generated, e.g., <code>app\models</code>',
            'queryClass' => 'This is the name of the ActiveQuery class to be generated. The class name should not contain
                the namespace part as it is specified in "ActiveQuery Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveQuery classes will be generated.',
            'queryBaseClass' => 'This is the base class of the new ActiveQuery class. It should be a fully qualified namespaced class name.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        $templates = ['model.php'];
        if ($this->queryClass !== null) {
            $templates[] = 'query.php';
        }

        return $templates;
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(
            parent::stickyAttributes(),
            [
                'ns',
                'db',
                'baseClass',
                'generateRelations',
                'generateJunctionRelationMode',
                'generateLabelsFromComments',
                'queryNs',
                'queryBaseClass',
                'useTablePrefix',
                'generateQuery',
                'useClassConstant',
            ]
        );
    }

    /**
     * Returns the `tablePrefix` property of the DB connection as specified
     *
     * @return string
     * @since 2.0.5
     * @see getDbConnection
     */
    public function getTablePrefix()
    {
        $db = $this->getDbConnection();

        return $db === null ? '' : $db->tablePrefix;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            // model:
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = $this->generateQuery ? $this->generateQueryClassName($modelClassName) : false;
            $tableRelations = isset($relations[$tableName]) ? $relations[$tableName] : [];
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $modelClassName,
                'queryClassName' => $queryClassName,
                'tableSchema' => $tableSchema,
                'properties' => $this->generateProperties($tableSchema),
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => $tableRelations,
                'relationsClassHints' => $this->generateRelationsClassHints($tableRelations, $this->generateQuery),
                'enum' => $this->getEnum($tableSchema->columns),
            ];
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                $this->render('model.php', $params)
            );

            // query:
            if ($queryClassName) {
                $params['className'] = $queryClassName;
                $params['modelClassName'] = $modelClassName;
                $files[] = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\', '/', $this->queryNs)) . '/' . $queryClassName . '.php',
                    $this->render('query.php', $params)
                );
            }
        }

        return $files;
    }

    /**
     * Generates the properties for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated properties (property => type)
     * @since 2.0.6
     */
    protected function generateProperties($table)
    {
        $properties = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $type = 'int';
                    break;
                case Schema::TYPE_BOOLEAN:
                    $type = 'bool';
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $type = 'float';
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $type = 'string';
                    break;
                default:
                    $type = $column->phpType;
            }
            if ($column->allowNull) {
                $type .= '|null';
            }
            $properties[$column->name] = [
                'type' => $type,
                'name' => $column->name,
                'comment' => $column->comment,
            ];
        }

        return $properties;
    }

    /**
     * Generates the attribute labels for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($table)
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if ($this->generateLabelsFromComments && !empty($column->comment)) {
                $labels[$column->name] = $column->comment;
            } elseif (!strcasecmp($column->name, 'id')) {
                $labels[$column->name] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                $labels[$column->name] = $label;
            }
        }

        return $labels;
    }

    /**
     * Generates the relation class hints for the relation methods
     * @param array $relations the relation array for single table
     * @param bool $generateQuery generates ActiveQuery class (for ActiveQuery namespace available)
     * @return array
     * @since 2.1.4
     */
    public function generateRelationsClassHints($relations, $generateQuery)
    {
        $result = [];
        foreach ($relations as $name => $relation) {
            // The queryNs options available if generateQuery is active
            if ($generateQuery) {
                $queryClassRealName = '\\' . $this->queryNs . '\\' . $relation[1];
                if (class_exists($queryClassRealName, true) && is_subclass_of($queryClassRealName, '\yii\db\BaseActiveRecord')) {
                    /** @var \yii\db\ActiveQuery $activeQuery */
                    $activeQuery = $queryClassRealName::find();
                    $activeQueryClass = $activeQuery::className();
                    if (strpos($activeQueryClass, $this->ns) === 0) {
                        $activeQueryClass = StringHelper::basename($activeQueryClass);
                    }
                    $result[$name] = '\yii\db\ActiveQuery|' . $activeQueryClass;
                } else {
                    $result[$name] = '\yii\db\ActiveQuery|' . (($this->ns === $this->queryNs) ? $relation[1] : '\\' . $this->queryNs . '\\' . $relation[1]) . 'Query';
                }
            } else {
                $result[$name] = '\yii\db\ActiveQuery';
            }
        }
        return $result;
    }

    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        $nullable = [];
        $defaultValues = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            } elseif ($column->allowNull && $column->defaultValue === null) {
                $nullable[] = $column->name;
            } elseif (is_scalar($column->defaultValue)) {
                if (array_key_exists($column->defaultValue, $defaultValues)) {
                    $defaultValues[$column->defaultValue] = [];
                }
                $defaultValues[$column->defaultValue][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];
        if (!empty($nullable)) {
            $rules[] = "[['" . implode("', '", $nullable) . "'], 'default', 'value' => null]";
        }
        if (!empty($defaultValues)) {
            foreach ($defaultValues as $defaultValue => $defaultValueColumns) {
                $defaultValue = is_numeric($defaultValue) ? $defaultValue : "'$defaultValue'";
                $rules[] = "[['" . implode("', '", $defaultValueColumns) . "'], 'default', 'value' => $defaultValue]";
            }
        }
        $driverName = $this->getDbDriverName();
        foreach ($types as $type => $columns) {
            if ($driverName === 'pgsql' && $type === 'integer') {
                $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => null]";
            }
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        $columnsEnum = $this->getEnum($table->columns);
        foreach ($columnsEnum as $fieldName => $columnEnum) {
            $rules['enum-' . $fieldName] = "['" . $fieldName . "', 'in', 'range' => array_keys(self::" . $columnEnum['funcOptsName'] . '())]';
        }

        $db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            $refClassNameResolution = $this->generateClassNameResolution($refClassName);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassNameResolution, 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }

    /**
     * Generates relations using a junction table by adding an extra via() or viaTable() depending on $generateViaRelationMode.
     * @param \yii\db\TableSchema the table being checked
     * @param array $fks obtained from the checkJunctionTable() method
     * @param array $relations
     * @return array modified $relations
     */
    private function generateManyManyRelations($table, $fks, $relations)
    {
        $db = $this->getDbConnection();


        foreach ($fks as $pair) {
            list($firstKey, $secondKey) = $pair;
            $table0 = $firstKey[0][0];
            $table1 = $secondKey[0][0];
            unset($firstKey[0][0], $secondKey[0][0]);
            $className0 = $this->generateClassName($table0);
            $className1 = $this->generateClassName($table1);
            $className0Resolution = $this->generateClassNameResolution($className0);
            $className1Resolution = $this->generateClassNameResolution($className1);
            $table0Schema = $db->getTableSchema($table0);
            $table1Schema = $db->getTableSchema($table1);

            // @see https://github.com/yiisoft/yii2-gii/issues/166
            if ($table0Schema === null || $table1Schema === null) {
                continue;
            }

            $link = $this->generateRelationLink(array_flip($secondKey[0]));
            $relationName = $this->generateRelationName($relations, $table0Schema, key($secondKey[0]), true);
            if ($this->generateJunctionRelationMode === self::JUNCTION_RELATION_VIA_TABLE) {
                $relations[$table0Schema->fullName][$relationName] = [
                    "return \$this->hasMany($className1Resolution, $link)->viaTable('"
                        . $this->generateTableName($table->name) . "', " . $this->generateRelationLink($firstKey[0]) . ');',
                    $className1,
                    true,
                ];
            } elseif ($this->generateJunctionRelationMode === self::JUNCTION_RELATION_VIA_MODEL) {
                $foreignRelationName = null;
                foreach ($relations[$table0Schema->fullName] as $key => $foreignRelationConfig) {
                    if ($foreignRelationConfig[3] == $firstKey[1]) {
                        $foreignRelationName = $key;
                        break;
                    }
                }
                if (empty($foreignRelationName)) {
                    throw new Exception('Foreign key for junction table not found.');
                }
                $relations[$table0Schema->fullName][$relationName] = [
                    "return \$this->hasMany($className1Resolution, $link)->via('"
                        . lcfirst($foreignRelationName) . "');",
                    $className1,
                    true,
                ];
            }

            $link = $this->generateRelationLink(array_flip($firstKey[0]));
            $relationName = $this->generateRelationName($relations, $table1Schema, key($firstKey[0]), true);
            if ($this->generateJunctionRelationMode === self::JUNCTION_RELATION_VIA_TABLE) {
                $relations[$table1Schema->fullName][$relationName] = [
                    "return \$this->hasMany($className0Resolution, $link)->viaTable('"
                        . $this->generateTableName($table->name) . "', " . $this->generateRelationLink($secondKey[0]) . ');',
                    $className0,
                    true,
                ];
            } elseif ($this->generateJunctionRelationMode === self::JUNCTION_RELATION_VIA_MODEL) {
                $foreignRelationName = null;
                foreach ($relations[$table1Schema->fullName] as $key => $foreignRelationConfig) {
                    if ($foreignRelationConfig[3] == $secondKey[1]) {
                        $foreignRelationName = $key;
                        break;
                    }
                }
                if (empty($foreignRelationName)) {
                    throw new Exception('Foreign key for junction table not found.');
                }
                $relations[$table1Schema->fullName][$relationName] = [
                    "return \$this->hasMany($className0Resolution, $link)->via('"
                        . lcfirst($foreignRelationName) . "');",
                    $className0,
                    true,
                ];
            } else {
                throw new InvalidConfigException('Unknown generateViaRelationMode ' . $this->generateJunctionRelationMode);
            }
        }

        return $relations;
    }

    /**
     * @return string[] all db schema names or an array with a single empty string
     * @throws NotSupportedException
     * @since 2.0.5
     */
    protected function getSchemaNames()
    {
        $db = $this->getDbConnection();

        if ($this->generateRelationsFromCurrentSchema) {
            if ($db->schema->defaultSchema !== null) {
                return [$db->schema->defaultSchema];
            }
            return [''];
        }

        $schema = $db->getSchema();
        if ($schema->hasMethod('getSchemaNames')) { // keep BC to Yii versions < 2.0.4
            try {
                $schemaNames = $schema->getSchemaNames();
            } catch (NotSupportedException $e) {
                // schema names are not supported by schema
            }
        }
        if (!isset($schemaNames)) {
            if (($pos = strpos($this->tableName, '.')) !== false) {
                $schemaNames = [substr($this->tableName, 0, $pos)];
            } else {
                $schemaNames = [''];
            }
        }
        return $schemaNames;
    }

    /**
     * @return array the generated relation declarations
     */
    protected function generateRelations()
    {
        if ($this->generateRelations === self::RELATIONS_NONE) {
            return [];
        }

        $db = $this->getDbConnection();
        $relations = [];
        $schemaNames = $this->getSchemaNames();
        foreach ($schemaNames as $schemaName) {
            foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
                $className = $this->generateClassName($table->fullName);
                $classNameResolution = $this->generateClassNameResolution($className);
                foreach ($table->foreignKeys as $foreignKey => $refs) {
                    $refTable = $refs[0];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ($refTableSchema === null) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[0]);
                    $fks = array_keys($refs);
                    $relName = $this->generateRelationNameFromDestinationTable ? $refTable : $fks[0];
                    $refClassName = $this->generateClassName($refTable);
                    $refClassNameResolution = $this->generateClassNameResolution($refClassName);

                    // Add relation for this table
                    $link = $this->generateRelationLink(array_flip($refs));
                    $relationName = $this->generateRelationName($relations, $table, $relName, false);
                    $relations[$table->fullName][$relationName] = [
                        "return \$this->hasOne($refClassNameResolution, $link);",
                        $refClassName,
                        false,
                        $table->fullName . '.' . $foreignKey
                    ];

                    // Add relation for the referenced table
                    $hasMany = $this->isHasManyRelation($table, $fks);
                    $link = $this->generateRelationLink($refs);
                    $relationName = $this->generateRelationName($relations, $refTableSchema, $className, $hasMany);
                    $relations[$refTableSchema->fullName][$relationName] = [
                        "return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($classNameResolution, $link);",
                        $className,
                        $hasMany,
                        $table->fullName . '.' . $foreignKey
                    ];
                }
            }

            foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
                if (($junctionFks = $this->checkJunctionTable($table)) === false) {
                    continue;
                }

                $relations = $this->generateManyManyRelations($table, $junctionFks, $relations);
            }
        }

        if ($this->generateRelations === self::RELATIONS_ALL_INVERSE) {
            $relations =  $this->addInverseRelations($relations);
        }

        foreach ($relations as &$relation) {
            ksort($relation);
        }

        return $relations;
    }

    /**
     * Adds inverse relations
     *
     * @param array $relations relation declarations
     * @return array relation declarations extended with inverse relation names
     * @since 2.0.5
     */
    protected function addInverseRelations($relations)
    {
        $db = $this->getDbConnection();
        $relationNames = [];

        $schemaNames = $this->getSchemaNames();
        foreach ($schemaNames as $schemaName) {
            foreach ($db->schema->getTableSchemas($schemaName) as $table) {
                $className = $this->generateClassName($table->fullName);
                foreach ($table->foreignKeys as $refs) {
                    $refTable = $refs[0];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ($refTableSchema === null) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[0]);
                    $fks = array_keys($refs);

                    $leftRelationName = $this->generateRelationName($relationNames, $table, $fks[0], false);
                    $relationNames[$table->fullName][$leftRelationName] = true;
                    $hasMany = $this->isHasManyRelation($table, $fks);
                    $rightRelationName = $this->generateRelationName(
                        $relationNames,
                        $refTableSchema,
                        $className,
                        $hasMany
                    );
                    $relationNames[$refTableSchema->fullName][$rightRelationName] = true;

                    $relations[$table->fullName][$leftRelationName][0] =
                        rtrim($relations[$table->fullName][$leftRelationName][0], ';')
                        . "->inverseOf('" . lcfirst($rightRelationName) . "');";
                    $relations[$refTableSchema->fullName][$rightRelationName][0] =
                        rtrim($relations[$refTableSchema->fullName][$rightRelationName][0], ';')
                        . "->inverseOf('" . lcfirst($leftRelationName) . "');";
                }
            }
        }
        return $relations;
    }

    /**
     * Determines if relation is of has many type
     *
     * @param TableSchema $table
     * @param array $fks
     * @return bool
     * @since 2.0.5
     */
    protected function isHasManyRelation($table, $fks)
    {
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        foreach ($uniqueKeys as $uniqueKey) {
            if (array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks)) === []) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generates the link parameter to be used in generating the relation declaration.
     * @param array $refs reference constraint
     * @return string the generated link parameter.
     */
    protected function generateRelationLink($refs)
    {
        $pairs = [];
        foreach ($refs as $a => $b) {
            $pairs[] = "'$a' => '$b'";
        }

        return '[' . implode(', ', $pairs) . ']';
    }

    /**
     * Checks if the given table is a junction table, that is it has at least one pair of unique foreign keys.
     * @param \yii\db\TableSchema the table being checked
     * @return array|bool all unique foreign key pairs if the table is a junction table,
     * or false if the table is not a junction table.
     */
    protected function checkJunctionTable($table)
    {
        if (count($table->foreignKeys) < 2) {
            return false;
        }
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        $result = [];
        // find all foreign key pairs that have all columns in an unique constraint
        $foreignKeyNames = array_keys($table->foreignKeys);
        $foreignKeys = array_values($table->foreignKeys);
        $foreignKeysCount = count($foreignKeys);

        for ($i = 0; $i < $foreignKeysCount; $i++) {
            $firstColumns = $foreignKeys[$i];
            unset($firstColumns[0]);

            for ($j = $i + 1; $j < $foreignKeysCount; $j++) {
                $secondColumns = $foreignKeys[$j];
                unset($secondColumns[0]);

                $fks = array_merge(array_keys($firstColumns), array_keys($secondColumns));
                foreach ($uniqueKeys as $uniqueKey) {
                    if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                        // save the foreign key pair
                        $result[] = [
                            [
                                $foreignKeys[$i],
                                $table->fullName . '.' . $foreignKeyNames[$i]
                            ],
                            [
                                $foreignKeys[$j],
                                $table->fullName . '.' . $foreignKeyNames[$j]
                            ]
                        ];
                        break;
                    }
                }
            }
        }
        return empty($result) ? false : $result;
    }

    /**
     * Generate a relation name for the specified table and a base name.
     * @param array $relations the relations being generated currently.
     * @param \yii\db\TableSchema $table the table schema
     * @param string $key a base name that the relation name may be generated from
     * @param bool $multiple whether this is a has-many relation
     * @return string the relation name
     */
    protected function generateRelationName($relations, $table, $key, $multiple)
    {
        static $baseModel;
        /* @var $baseModel \yii\db\ActiveRecord */
        if ($baseModel === null) {
            $baseClass = $this->baseClass;
            if ($baseClass === 'weebz\yii2basics\models\ModelCommon') {
                $baseModel = new class extends \yii\db\ActiveRecord {
                    public static function tableName()
                    {
                        return 'dummy_table_for_gii';
                    }
                };
            } else {
                $baseModel = new $baseClass();
            }
            $baseClassReflector = new \ReflectionClass($baseClass);
            if ($baseClassReflector->isAbstract()) {
                $baseClassWrapper =
                    'namespace ' . __NAMESPACE__ . ';' .
                    'class GiiBaseClassWrapper extends \\' . $baseClass . ' {' .
                    'public static function tableName(){' .
                    'return "' . addslashes($table->fullName) . '";' .
                    '}' .
                    '};' .
                    'return new GiiBaseClassWrapper();';
                $baseModel = eval($baseClassWrapper);
            } else {
                $baseModel = new $baseClass();
            }
            $baseModel->setAttributes([]);
        }

        if (!empty($key) && strcasecmp($key, 'id')) {
            if (substr_compare($key, 'id', -2, 2, true) === 0) {
                $key = rtrim(substr($key, 0, -2), '_');
            } elseif (substr_compare($key, 'id_', 0, 3, true) === 0) {
                $key = ltrim(substr($key, 3, strlen($key)), '_');
            }
        }
        if ($multiple) {
            $key = Inflector::pluralize($key);
        }
        $name = $rawName = Inflector::id2camel($key, '_');
        $i = 0;
        while ($baseModel->hasProperty(lcfirst($name))) {
            $name = $rawName . ($i++);
        }
        while (isset($table->columns[lcfirst($name)])) {
            $name = $rawName . ($i++);
        }
        while (isset($relations[$table->fullName][$name])) {
            $name = $rawName . ($i++);
        }

        return $name;
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "db".');
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "db" application component must be a DB connection instance.');
        }
    }

    /**
     * Validates the namespace.
     *
     * @param string $attribute Namespace variable.
     */
    public function validateNamespace($attribute)
    {
        $value = $this->$attribute;
        $value = ltrim($value, '\\');
        $path = Yii::getAlias('@' . str_replace('\\', '/', $value), false);
        if ($path === false) {
            $this->addError($attribute, 'Namespace must be associated with an existing directory.');
        }
    }

    /**
     * Validates the [[modelClass]] attribute.
     */
    public function validateModelClass()
    {
        if ($this->isReservedKeyword($this->modelClass)) {
            $this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
        }
        if ((empty($this->tableName) || substr_compare($this->tableName, '*', -1, 1)) && $this->modelClass == '') {
            $this->addError('modelClass', 'Model Class cannot be blank if table name does not end with asterisk.');
        }
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        if (strpos($this->tableName, '*') !== false && substr_compare($this->tableName, '*', -1, 1)) {
            $this->addError('tableName', 'Asterisk is only allowed as the last character.');

            return;
        }
        $tables = $this->getTableNames();
        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
        } else {
            foreach ($tables as $table) {
                $class = $this->generateClassName($table);
                if ($this->isReservedKeyword($class)) {
                    $this->addError('tableName', "Table '$table' will generate a class which is a reserved PHP keyword.");
                    break;
                }
            }
        }
    }

    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    protected function getTableNames()
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            if (($pos = strrpos($this->tableName, '.')) !== false) {
                $schema = substr($this->tableName, 0, $pos);
                $pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
            } else {
                $schema = '';
                $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
            }

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
                }
            }
        } elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
            $tableNames[] = $this->tableName;
            $this->classNames[$this->tableName] = $this->modelClass;
        }

        return $this->tableNames = $tableNames;
    }

    /**
     * Generates the table name by considering table prefix.
     * If [[useTablePrefix]] is false, the table name will be returned without change.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated table name
     */
    public function generateTableName($tableName)
    {
        if (!$this->useTablePrefix) {
            return $tableName;
        }

        $db = $this->getDbConnection();
        if (preg_match("/^{$db->tablePrefix}(.*?)$/", $tableName, $matches)) {
            $tableName = '{{%' . $matches[1] . '}}';
        } elseif (preg_match("/^(.*?){$db->tablePrefix}$/", $tableName, $matches)) {
            $tableName = '{{' . $matches[1] . '%}}';
        }
        return $tableName;
    }

    /**
     * Generates a class name from the specified table name.
     * @param string $tableName the table name (which may contain schema prefix)
     * @param bool $useSchemaName should schema name be included in the class name, if present
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        if (!empty($this->classNames[$tableName])) {
            return $this->classNames[$tableName];
        }

        $schemaName = '';
        $fullTableName = $tableName;
        if (($pos = strrpos($tableName, '.')) !== false) {
            if (($useSchemaName === null && $this->useSchemaName) || $useSchemaName) {
                $schemaName = substr($tableName, 0, $pos) . '_';
            }
            $tableName = substr($tableName, $pos + 1);
        }

        $db = $this->getDbConnection();
        $patterns = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        if (strpos($this->tableName, '*') !== false) {
            $pattern = $this->tableName;
            if (($pos = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                break;
            }
        }

        if ($this->standardizeCapitals) {
            $schemaName = ctype_upper(preg_replace('/[_-]/', '', $schemaName)) ? strtolower($schemaName) : $schemaName;
            $className = ctype_upper(preg_replace('/[_-]/', '', $className)) ? strtolower($className) : $className;
            $this->classNames[$fullTableName] = Inflector::camelize(Inflector::camel2words($schemaName . $className));
        } else {
            $this->classNames[$fullTableName] = Inflector::id2camel($schemaName . $className, '_');
        }

        if ($this->singularize) {
            $this->classNames[$fullTableName] = Inflector::singularize($this->classNames[$fullTableName]);
        }

        return $this->classNames[$fullTableName];
    }

    /**
     * Action to generate class name.
     * @return string
     * @since 2.2.2
     */
    public function actionGenerateClassName()
    {
        return $this->generateClassName($this->tableName);
    }

    /**
     * Generates a query class name from the specified model class name.
     * @param string $modelClassName model class name
     * @return string generated class name
     */
    protected function generateQueryClassName($modelClassName)
    {
        $queryClassName = $this->queryClass;
        if (empty($queryClassName) || strpos($this->tableName, '*') !== false) {
            $queryClassName = $modelClassName . 'Query';
        }
        return $queryClassName;
    }

    /**
     * Returns the database connection as specified by [[db]].
     *
     * @return Connection|null database connection instance
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }

    /**
     * Returns the driver name of [[db]] connection.
     *
     * @return string|null driver name of db connection.
     * @since 2.0.6
     */
    protected function getDbDriverName()
    {
        $db = $this->getDbConnection();

        return $db instanceof Connection ? $db->driverName : null;
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     * @param \yii\db\TableSchema $table the table schema
     * @param string[] $columns columns to check for autoIncrement property
     * @return bool whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental($table, $columns)
    {
        foreach ($columns as $column) {
            if (isset($table->columns[$column]) && $table->columns[$column]->autoIncrement) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepares ENUM field values.
     *
     * @param ColumnSchema[] $columns
     *
     * @return array
     */
    public function getEnum($columns)
    {
        $enum = [];
        foreach ($columns as $column) {
            if (!$this->isEnum($column)) {
                continue;
            }

            $columnCamelName = Inflector::id2camel($column->name, '_');
            $enum[$column->name]['funcOptsName'] = 'opts' . $columnCamelName;
            $enum[$column->name]['isFunctionPrefix'] = 'is' . $columnCamelName;
            $enum[$column->name]['setFunctionPrefix'] = 'set' . $columnCamelName . 'To';
            $enum[$column->name]['displayFunctionPrefix'] = 'display' . $columnCamelName;
            $enum[$column->name]['columnName'] = $column->name;
            $enum[$column->name]['values'] = [];

            foreach ($column->enumValues as $value) {

                $constantName = strtoupper(Inflector::slug($column->name . ' ' . $value, '_'));
                $label = Inflector::camel2words($value);

                $enum[$column->name]['values'][] = [
                    'value' => $value,
                    'constName' => $constantName,
                    'label' => $label,
                    'functionSuffix' => Inflector::id2camel(Inflector::slug($value))
                ];
            }
        }

        return $enum;
    }

    /**
     * Checks if column is of ENUM type.
     *
     * @param ColumnSchema $column Column instance
     * @return bool
     */
    protected function isEnum($column)
    {
        return !empty($column->enumValues) || stripos($column->dbType, 'ENUM') === 0;
    }

    /**
     * Returns the class name resolution
     * @param string $class
     * @return string
     * @see $useClassConstant
     * @since 2.2.5
     */
    protected function generateClassNameResolution($class)
    {
        return $class . '::class' . ($this->useClassConstant ? '' : 'Name()');
    }
}
