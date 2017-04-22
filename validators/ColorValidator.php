<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class ColorValidator extends \yii\validators\Validator
{

    /**
     * @var string the color rule format
     */
    public $format = 'hex';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be an {format} color.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $valid = false;
        if ($this->format == 'hex') {
            $valid = preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i', $value);
        } else if ($this->format == 'rgb') {
            $valid = preg_match('/^(rgb)?\(?([01]?\d\d?|2[0-4]\d|25[0-5])(\W+)([01]?\d\d?|2[0-4]\d|25[0-5])\W+(([01]?\d\d?|2[0-4]\d|25[0-5])\)?)$/i', $value);
        }

        return $valid ? null : [$this->message, [
            'format' => $this->format
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
            'format' => $this->format,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'format' => $this->format
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);
        return 'yii.validation.color(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}