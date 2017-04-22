<?php

namespace pfs\yii\grid;

use Yii;
use yii\helpers\Html;

class InputColumn extends DataColumn
{
    /** 
     * @string Form field
     */
    public $activeField;

    /**
     * @string Detect url value 'grid-mode'
     */
    public $inputModeName = 'form-inline';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->disableFilter();
    }

    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {
        $displayValue = parent::renderDataCell($model, $key, $index);

        // Field not set
        if (!$this->activeField || ($this->activeField instanceof \Closure) === false) {
            return $displayValue;
        }

        // Is form inline mode
        $isInputMode = Yii::$app->request->get('grid-mode', null) == $this->inputModeName;

        // Is form inline
        if (is_array($key)) {
            $isFormInput = true;
            foreach ($key as $name => $value) {
                if (Yii::$app->request->get($name, null) != $value) {
                    $isFormInput = false;
                    break;
                }
            }
        } else {
            $isFormInput = Yii::$app->request->get('id', null) == $key;
        }

        if ($isInputMode && $isFormInput && ($displayForm = $this->renderActiveField($model, $key, $index))) {
            $displayValue = $displayForm;
        }

        return $displayValue;
    }

    /**
     * Returns the form field for cell value
     * @param $model The data model
     * @param $key mixed The key associated with the data model
     * @param $index int The zero-based index of the data model among the models array returned by [[GridView::dataProvider]]
     * @return string the data cell value
     */
    public function renderActiveField($model, $key, $index)
    {
        // Form field
        $displayForm = call_user_func($this->activeField, $model, $key, $index);
        if (!empty($displayForm)) {

            // Options
            if ($this->contentOptions instanceof \Closure) {
                $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
            } else {
                $options = $this->contentOptions;
            }

            return Html::tag('td', $displayForm, $options);
        }

        return false;
    }

    /**
     * Register javascript and html options for disable filter
     */
    protected function disableFilter()
    {
        if (Yii::$app->request->get('grid-mode', null) == $this->inputModeName) {
            $this->filterInputOptions['disabled'] = 'disabled';
            $this->filterInputOptions['readonly'] = 'readonly';
            Html::addCssClass($this->filterInputOptions, 'disabled');
            $this->grid->view->registerJs(
"jQuery('#{$this->grid->filterRowOptions['id']} > td > input, #{$this->grid->filterRowOptions['id']} > td > select').each(function(index, el) {
    $(el).attr({disabled: 'disabled', readonly: 'readonly'}).addClass('disabled');
});");
        }
    }
}