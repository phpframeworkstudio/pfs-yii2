<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class DecimalValidator extends \yii\validators\Validator
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be an decimal.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        return ((bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str)) ? null : [$this->message, []];
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
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute)
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);
        return 'yii.validation.decimal(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}