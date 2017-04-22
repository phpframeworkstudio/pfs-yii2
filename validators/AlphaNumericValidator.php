<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class AlphaNumericValidator extends \yii\validators\Validator
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} may only contain alpha-numeric characters.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        return ctype_alnum((string) $value) ? null : [$this->message, []];
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
        return 'yii.validation.alphaNumeric(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}