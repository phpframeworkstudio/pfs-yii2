<?php

namespace pfs\yii\widgets;

use Yii;
use yii\jui\JuiAsset;
use pfs\yii\helpers\Html;

class AjaxTextInput extends BaseAjaxInput
{

    /**
     * @var array The hidden value options.
     */
    public $hiddenOptions = [];

    /**
     * @var string The dropdown type initialize.
     */
    public $type = 'text';
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->options['id'] = $this->getId();
        if (empty($this->hiddenOptions['id'])) {
            $this->hiddenOptions['id'] = Html::getInputId($this->model, $this->attribute);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $result = [];
        $result[] = Html::beginTag('div', ['class' => 'pfs__text-autocomplete']);
        $result[] = Html::textInput(null, null, $this->options);
        $result[] = Html::activeHiddenInput($this->model, $this->attribute, $this->hiddenOptions);
        $result[] = Html::endTag('div');

        $this->options['valueId'] = $this->hiddenOptions['id'];
        $this->registerClientScript();
        JuiAsset::register($this->view);


        return implode("\n", $result);
    }
}