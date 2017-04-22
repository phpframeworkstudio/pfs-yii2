<?php

namespace pfs\yii\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
class DetailView extends \yii\widgets\DetailView
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        ob_start();
        parent::run();
        $parent = ob_get_clean();

        return Html::tag('div', $parent, [
            'id' => $this->getId() .'-container'
        ]);
    }

    /**
     * Renders a single attribute.
     * @param array $attribute the specification of the attribute to be rendered.
     * @param integer $index the zero-based index of the attribute in the [[attributes]] array
     * @return string the rendering result
     */
    protected function renderAttribute($attribute, $index)
    {
        if (is_string($this->template)) {
            $value = $this->formatter->format($attribute['value'], $attribute['format']);

            $popoverContent = '';
            $popoverValue = ArrayHelper::getValue($attribute, 'popoverValue', false);
            $popoverSelectorValue = ArrayHelper::getValue($attribute, 'popoverSelectorValue', false);
            $popoverClass = uniqid('popover_');

            if ($popoverValue) {
                $value = Html::tag('span', $value, [
                    'class' => $popoverClass
                ]);

                $innerContent = Html::tag('span', $this->getPopoverValue($this->model, $popoverValue), [
                    'class' => 'popover-preview'
                ]);
                $popoverContent = Html::tag('span', $innerContent, [
                    'popover-content' => $popoverClass,
                    'style' => 'display: none'
                ]);
                $this->registerPopoverScript($popoverClass, '[popover-content="'. $popoverClass .'"]');
            } else if ($popoverSelectorValue) {
                $value = Html::tag('span', $value, [
                    'class' => $popoverClass
                ]);
                $this->registerPopoverScript($popoverClass, $popoverSelectorValue);
            }

            $captionOptions = Html::renderTagAttributes(ArrayHelper::getValue($attribute, 'captionOptions', []));
            $contentOptions = Html::renderTagAttributes(ArrayHelper::getValue($attribute, 'contentOptions', []));

            if (is_array($captionOptions)) {
                $captionOptions = Html::cssStyleFromArray($captionOptions);
            }
            
            if (is_array($contentOptions)) {
                $contentOptions = Html::cssStyleFromArray($contentOptions);
            }

            return strtr($this->template, [
                '{label}' => $attribute['label'],
                '{value}' => $value . $popoverContent,
                '{captionOptions}' => $captionOptions,
                '{contentOptions}' =>  $contentOptions,
            ]);
        } else {
            return call_user_func($this->template, $attribute, $index, $this);
        }
    }

    /**
     * Generating value for popover
     * @param mixed $model the data model being rendered
     * @param mixed $key the key associated with the data model
     * @param integer $index the zero-based index of the data item among the item array returned by [[GridView::dataProvider]].
     * @return string the string result
     */
    protected function getPopoverValue($model, $popoverValue)
    {
        if (!$popoverValue) {
            return false;
        } else {
            $value = $popoverValue;
            try {
                if ($value instanceof \Closure) {
                    $value = call_user_func($popoverValue, $model);
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
        $json = [
            'html' => true,
            'container' => '#'. $this->getId() .'-container',
            'placement' => new JsExpression("function(c, s) { var p = jQuery(s).position(); return p.left > 515 ? 'left' : p.left < 515 ? 'right' : p.top < 300 ? 'bottom' : 'top';}"),
            'trigger' => 'hover', 
            'content' => new JsExpression("jQuery('{$target}').html()")
        ];
        $js = "jQuery('.{$class}').popover(". Json::encode($json) .");";
        Yii::$app->controller->view->registerJs($js);
    }
}