<?php

namespace pfs\yii\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use pfs\yii\helpers\Html;

class AjaxDropDownList extends BaseAjaxInput
{
    /**
     * @var array The option data items. The array keys are option values, and the array values.
     */
    public $items = [];

    /**
     * @var boolean The dropdown multiple
     */ 
    public $multiple = false;

    /**
     * @var string The dropdown type initialize.
     */
    public $type = 'dropdown';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!empty(($placeholder = $this->placeholder))) {
            $this->options['prompt'] = $placeholder;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->options['multiple']) && $this->multiple) {
            $this->options['multiple'] = $this->multiple;
        }
        $this->registerClientScript();
        return Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
    }
}