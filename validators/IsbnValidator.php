<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class IsbnValidator extends \yii\validators\Validator
{

    /**
     * @var string The isbn type
     */
    public $type;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            if ($this->type === null) {
                $this->message = Yii::t('yii', '{attribute} must contain a valid ISBN.');
            } elseif ($this->type == 'isbn10') {
                $this->message = Yii::t('yii', '{attribute} must contain a valid ISBN10.');
            } elseif ($this->type == 'isbn13') {
                $this->message = Yii::t('yii', '{attribute} must contain a valid ISBN13.');
            } else {
                throw new InvalidConfigException('The "type" property failed.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $valid = false;
        $isbn = false;

        // check params
        if (($this->type === null || $this->type == 'isbn10') && (
            preg_match('/^\d{9}[\dX]$/', $value) || 
            preg_match('/^(\d+)-(\d+)-(\d+)-([\dX])$/', $value) || 
            preg_match('/^(\d+)\s(\d+)\s(\d+)\s([\dX])$/', $value)
        )) {
            $isbn = 'isbn10';
        } else if (($this->type === null || $this->type == 'isbn13') && (
            preg_match('/^(978|979)\d{9}[\dX]$/', $value) || 
            preg_match('/^(978|979)-(\d+)-(\d+)-(\d+)-([\dX])$/', $value) ||
            preg_match('/^(978|979)\s(\d+)\s(\d+)\s(\d+)\s([\dX])$/', $value)
        )) {
            $isbn = 'isbn13';
        }

        $value = preg_replace('/[^0-9x]/i', '', $value);
        if ($isbn == 'isbn10') {
            $chars = str_split($value);
            if (strtoupper($chars[9]) == 'X') {
                $chars[9] = 10;
            }

            $sum = 0;
            for($i = 0; $i < count($chars); $i++) {
                $sum += ((10 - $i) * (int) $chars[$i]);
            }

            $valid = (($sum % 11) == 0);
        } elseif ($isbn == 'isbn13') {
            $chars = str_split($value);
            $sum = 0;
            $digit = 0;
            $check = false;
            for($i = 0; $i < 12; $i++) {
                $digit = (int) $chars[$i];
                if ($i % 2) {
                    $sum += 3 * $digit;
                } else {
                    $sum += $digit;
                }
            }

            $check = (10 - ($sum % 10)) % 10;
            $valid = ($check == $chars[count($chars) - 1]);
        }

        return $valid ? null : [$this->message, [
            'type' => $this->type
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
            'type' => $this->type,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'type' => $this->type,
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);
        return 'yii.validation.isbn(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}