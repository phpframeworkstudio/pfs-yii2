<?php

namespace pfs\yii\widgets;

use yii\helpers\ArrayHelper;
use pfs\yii\helpers\Html;

class AjaxCheckboxList extends BaseAjaxInput
{
    public $items = [];

    /**
     * @var string The checkbox type initialize.
     */
    public $type = 'checkbox';

    public function run()
    {
        Html::addCssClass($this->options, 'pfs__ajax-checkbox-list');

        if (!isset($this->options['name'])) {
            $this->options['name'] = Html::getInputName($this->model, $this->attribute);
        }

        if (substr($this->options['name'], -2) !== '[]') {
            $this->options['name'] .= '[]';
        }

        $this->registerClientScript();

        ArrayHelper::remove($this->options, 'name');

        return Html::activeCheckboxList($this->model, $this->attribute, $this->items, $this->options);
    }
}