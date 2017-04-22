<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class ContainsValidator extends \yii\validators\Validator
{
    /**
     * @var string The rule mode of "start with" or "end with" or "contain".
     * In default config, this mode set "contain"
     */
    public $mode = 'contain';

    /**
     * @var string The contain value for rule
     */
    public $contain;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->mode === null) {
            throw new InvalidConfigException('The "mode" property failed.');
        }

        if ($this->mode === null) {
            throw new InvalidConfigException('The "contain" property failed.');
        }

        if ($this->message === null) {
            if ($this->mode == 'contain') {
                $this->message = Yii::t('yii', '{attribute} must be contain "{contain}".');
            } elseif ($this->mode == 'startWith') {
                $this->message = Yii::t('yii', '{attribute} must be start with "{contain}".');
            } elseif ($this->mode == 'endWith') {
                $this->message = Yii::t('yii', '{attribute} must be end with "{contain}".');
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if ($this->mode == 'contain') {
            $valid = preg_match('/'. $this->contain .'/', $value);
        } elseif ($this->mode == 'startWith') {
            $valid = preg_match('/^'. $this->contain .'/', $value);
        } elseif ($this->mode == 'endWith') {
            $valid = preg_match('/'. $this->contain .'$/', $value);
        }

        return $valid ? null : [$this->message, [
            'contain' => $this->contain,
            'mode' => $this->mode
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
            'mode' => $this->mode,
            'contain' => $this->contain,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'contain' => $this->contain,
                'mode' => $this->mode
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);
        return 'yii.validation.contains(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}