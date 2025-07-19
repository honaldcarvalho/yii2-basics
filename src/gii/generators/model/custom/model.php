<?php

use yii\helpers\StringHelper;

/* @var $generator yii\gii\generators\model\Generator */
/* @var $className string */
/* @var $tableName string */
/* @var $properties array */
/* @var $labels string[] */
/* @var $rules array */
/* @var $relations array */

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->ns, '\\')) ?>;

use weebz\yii2basics\models\ModelCommon;
use Yii;

<?php foreach ($relations as $relation): ?>
use <?= $relation[1] ?>;
<?php endforeach; ?>

/**
 * This is the model class for table "<?= $tableName ?>".
 *
<?php foreach ($properties as $property): ?>
 * @property <?= $property['type'] ?> $<?= $property['name'] ?> <?= $property['comment'] ?>

<?php endforeach; ?>
 */
class <?= $className ?> extends ModelCommon
{
    public $verGroup = false;

    public static function tableName()
    {
        return '<?= $tableName ?>';
    }

    public function rules()
    {
        return <?= $generator->generateString($rules) ?>;
    }

    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            '<?= $name ?>' => Yii::t('app', '<?= $label ?>'),
<?php endforeach; ?>
        ];
    }

<?php foreach ($relations as $name => $relation): ?>
    /**
     * @return \yii\db\ActiveQuery
     */
    public function <?= $name ?>()
    {
        return $this-><?= $relation[0] ?>;
    }

<?php endforeach; ?>
}
