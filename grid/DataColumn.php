<?php

namespace pfs\yii\grid;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

class DataColumn extends \yii\grid\DataColumn
{
    /**
     * @var string|closure popover value from models or closure
     */
    public $popoverValue;

    /**
     * @var string the css selector for popover value
     */
    public $popoverSelectorValue;

    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {
        if ($this->contentOptions instanceof \Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = $this->contentOptions;
        }
        $value = $this->renderDataCellContent($model, $key, $index);

        $popoverClass = uniqid('popover_');
        if ($this->popoverValue) {
            $value = Html::tag('span', $value, ['class' => $popoverClass]);

            $innerContent = Html::tag('span', $this->getPopoverValue($model, $key, $index), [
                'class' => 'popover-preview'
            ]);
            $value .= Html::tag('span', $innerContent, [
                'popover-content' => $popoverClass,
                'style' => 'display: none',
            ]);
            $this->registerPopoverScript($popoverClass, '[popover-content="'. $popoverClass .'"]');
        } else if ($this->popoverSelectorValue) {
            $value = Html::tag('span', $value, [
                'class' => $popoverClass
            ]);
            $this->registerPopoverScript($popoverClass, $this->popoverSelectorValue);
        }

        return Html::tag('td', $value, $options);
    }

    /**
     * Generating value for popover
     * @param mixed $model the data model being rendered
     * @param mixed $key the key associated with the data model
     * @param integer $index the zero-based index of the data item among the item array returned by [[GridView::dataProvider]].
     * @return string the string result
     */
    protected function getPopoverValue($model, $key, $index)
    {
        if (!$this->popoverValue) {
            return false;
        } else {
            $value = $this->popoverValue;
            try {
                if ($value instanceof \Closure) {
                    $value = call_user_func($this->popoverValue, $model, $key, $index, $this);
                } else {
                    $value = ArrayHelper::getValue($model, $value, $value);
                }
            } catch(\Exception $e) {
                // exception
            }

            return $value;
        }
    }

    /**
     * Registers the needed JavaScript.
     * @param string $id set selector id for popover container
     * @param string $target set selector for popover value
     */
    protected function registerPopoverScript($class, $target)
    {
        $containerId = $this->grid->options['id'];

        $json = [
            'html' => true,
            'container' => '#'. $containerId,
            'placement' => new JsExpression("function(c, s) { var p = jQuery(s).position(); return p.left > 515 ? 'left' : p.left < 515 ? 'right' : p.top < 300 ? 'bottom' : 'top';}"),
            'trigger' => 'hover', 
            'content' => new JsExpression("jQuery('{$target}').html()")
        ];
        $js = "jQuery('.{$class}').popover(". Json::encode($json) .");";
        Yii::$app->controller->view->registerJs($js);
    }
}