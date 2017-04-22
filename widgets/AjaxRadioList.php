<?php

namespace pfs\yii\widgets;

use yii\helpers\ArrayHelper;
use pfs\yii\helpers\Html;

class AjaxRadioList extends BaseAjaxInput
{
    public $items = [];

    /**
     * @var string The checkbox type initialize.
     */
    public $type = 'radio';

    public function run()
    {
        Html::addCssClass($this->options, 'pfs__ajax-radio-list');

        if (!isset($this->options['name'])) {
            $this->options['name'] = Html::getInputName($this->model, $this->attribute);
        }
        $this->registerClientScript();

        ArrayHelper::remove($this->options, 'name');
        return Html::activeRadioList($this->model, $this->attribute, $this->items, $this->options);
    }
}