<?php

namespace pfs\yii\validators;

use Yii;
use yii\base\InvalidConfigException;

class CreditCardsValidator extends \yii\validators\Validator
{

    /**
     * @var array The credit cards rule
     */
    public $formats = [];

    /**
     * @var string The credit card rule
     */
    public $format;

    /**
     * @var array The credit cards regex rule
     */
    public $creditCardMatch = [
        'AMERICAN_EXPRESS' => '((34|37)([0-9]{13}))',
        'DANKORT' => '((5019)([0-9]{12}))',
        'DINERS_CLUB' => '(((300|301|302|303|304|305|309)([0-9]{11}))|((36|38|39)([0-9]{12})))',
        'DINERS_CLUB_US' => '((54|55)([0-9]{14}))',
        'DISCOVER' => '(((622126|622127|622128|622129|622920|622921|622922|622923|622924|622925)([0-9]{10}))|((62213|62214|62215|62216|62217|62218|62219|62290|62291)([0-9]{11}))|((6222|6223|6224|6225|6226|6227|6228|6011)([0-9]{12}))|((644|645|646|647|648|649)([0-9]{13}))|((65)([0-9]{14})))',
        'ELO' => '(((4011|4312|4389|4514|4573|4576|5041|5066|5067|6277|6362|6363|6516|6550)([0-9]{12}))|((509|650)([0-9]{13})))',
        'JCB' => '(((3528|3529)([0-9]{12}))|((353|354|355|356|357|358)([0-9]{13})))',
        'LASER' => '((6304|6706|6771|6709)([0-9]{12,15}))',
        'MAESTRO' => '((5018|5020|5038|5868|6304|6759|6761|6762|6763|6764|6765|6766)([0-9]{8,15}))',
        'MASTERCARD' => '((51|52|53|54|55)([0-9]{14}))',
        'SOLO' => '((6334|6767)(([0-9]{12})|([0-9]{14,15})))',
        'UNIONPAY' => '(((622126|622127|622128|622129|622920|622921|622922|622923|622924|622925)([0-9]{10,13}))|((62213|62214|62215|62216|62217|62218|62219|62290|62291)([0-9]{11,14}))|((6222|6223|6224|6225|6226|6227|6228)([0-9]{12,15})))',
        'VISA' => '((4)([0-9]{15}))',
        'VISA_ELECTRON' => '(((4026|4405|4508|4844|4913|4917)([0-9]{12}))|((417500)([0-9]{10})))'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (count($this->formats) === 0 && $this->format === null) {
            if ($this->format === null) {
                throw new InvalidConfigException('The "format" property failed.');
            } else {
                throw new InvalidConfigException('The "formats" property failed.');
            }
        }

        if ($this->message === null) {
            if ($this->format !== null) {
                $this->message = Yii::t('yii', '{attribute} must be a valid credit card "{format}".');
            } else {
                $this->message = Yii::t('yii', '{attribute} must be a valid credit cards.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        // single format
        if ($this->format !== null) {
            $this->formats = [$this->format];
        }

        $valid = false;
        if (count($this->formats)) {
            $rgxArr = [];
            foreach ($this->formats as $format) {
                $format = strtoupper(trim($format));
                if (isset($this->creditCardMatch[$format])) {
                    $rgxArr[] = $this->creditCardMatch[$format];
                }
            }
            $valid = preg_match('/^('. implode('|', $rgxArr) .')$/', $value);
        }

        return $valid ? null : [$this->message, [
            'format' => $this->format,
            'formats' => implode(", ", $this->formats)
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
            'formats' => $this->formats,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'format' => $this->format,
                'formats' => implode(", ", $this->formats)
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);
        return 'yii.validation.creditCards(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }
}