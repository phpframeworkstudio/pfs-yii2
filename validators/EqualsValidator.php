<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class EqualsValidator extends \yii\validators\Validator
{
    /**
     * @var boolean Change validator mode to Not Equals
     */
    public $notEquals = false;

	/**
	 * @var array The define default values
	 */	
	public $values = [];

	/**
	 * @var boolean The value on case sensitive
	 */
	public $caseSensitive = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->values) || !is_array($this->values)) {
            throw new InvalidConfigException('The "values" property failed.');
        }

        if (!is_bool($this->caseSensitive)) {
            throw new InvalidConfigException('The "caseSensitive" property failed.');
        }

        if ($this->message === null) {
            if ($this->notEquals) {
                $this->message = Yii::t('yii', '{attribute} invalid, should not be the same as "{values}".');
            } else {
                $this->message = Yii::t('yii', '{attribute} invalid, select one "{values}".');
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
    	$values = []; // trim
    	foreach ($this->values as $val) {
    		if ($this->caseSensitive) {
	    		array_push($values, trim($val));
	    	} else {
	    		array_push($values, strtolower(trim($val)));
	    	}
    	}

    	if (!$this->caseSensitive) {
    		$value = strtolower(trim($value));
    	}

    	$valid = in_array($value, $values);

        // inverse
        if ($this->notEquals) {
            $valid = $valid ? false : true;
        }

    	return $valid ? null : [$this->message, [
    		'values' => implode(", ", $this->values)
    	]];
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {    	
        $value = $model->$attribute;
        $valid = $this->validateValue($value);
        if ($valid !== null) {
        	$this->addError($model, $attribute, $valid[0], $valid[1]);
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $options = [
            'values' => $this->values,
            'caseSensitive' => $this->caseSensitive,
            'notEquals' => $this->notEquals,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
    			'values' => implode(", ", $this->values)
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);
        return 'yii.validation.equals(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}