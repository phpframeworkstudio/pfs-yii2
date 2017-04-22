<?php

namespace pfs\yii\grid;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use pfs\yii\widgets\ButtonGroup;

class InputActionColumn extends ActionColumn
{
    /**
     * @string Detect url value 'grid-mode'
     */
    public $inputModeName = 'form-inline';

    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {
        $displayValue = parent::renderDataCell($model, $key, $index);

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

        if ($isInputMode && $isFormInput && ($displayForm = $this->renderFormButton($model, $key, $index))) {
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
    public function renderFormButton($model, $key, $index)
    {
        // Options
        if ($this->contentOptions instanceof \Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = $this->contentOptions;
        }

        $cancelUrl = Yii::$app->request->queryParams;
        $cancelUrl = array_merge($cancelUrl, (is_array($key) ? $key : ['id' => (string) $key]));
        $cancelUrl[0] = 'index';
        ArrayHelper::remove($cancelUrl, 'grid-mode');

        $buttons = ButtonGroup::widget([
            'encodeLabels' => false,
            'buttons' => [
                [
                    'label' => Html::tag('i', '', ['class' => 'fa fa-check']),
                    'tagName' => 'button',
                    'options' => [
                        'class' => 'btn btn-default',
                        'type' => 'submit'
                    ]
                ],
                [
                    'label' => Html::tag('i', '', ['class' => 'fa fa-close']),
                    'url' => $cancelUrl,
                    'options' => [
                        'class' => 'btn btn-default'
                    ]
                ]
            ]
        ]);

        return Html::tag('td', $buttons, $options);
    }
}