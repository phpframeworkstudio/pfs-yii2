<?php

namespace pfs\yii\grid;

use Yii;
use yii\helpers\Url;
use yii\grid\ActionColumn as BaseActionColumn;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonDropdown;
use pfs\yii\helpers\Html;

class ActionColumn extends BaseActionColumn
{
    const ACTION_DEFAULT = 0;
    const ACTION_DROPDOWN = 1;
    const ACTION_LINKS = 2;
    const ACTION_BUTTONS = 3;

    /**
     * @var string Icon class for dropdown button
     */
    public $iconCls = 'fa fa-fw fa-bars';

    /**
     * @var integer set action buttons view.
     */
    public $type = 0;

    /**
     * @var string the button size
     */
    public $buttonSize = 'md';

    /**
     * @var array the button group options
     */
    public $buttonContainerOptions = [];

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        // reposition
        $buttons = $this->buttons;
        $this->buttons = [];

        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key) {
                $options = array_merge([
                    'title' => Yii::t('app', 'Detail'),
                    'aria-label' => Yii::t('app', 'Detail'),
                    'data-pjax' => '0',
                ], $this->buttonOptions);
                $text = Html::iconCls('fa fa-fw fa-eye');
                $text .= $this->type == self::ACTION_DROPDOWN ? Yii::t('app', 'Detail') : '';
                return Html::a($text, $url, $options);
            };
        }

        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key) {
                $options = array_merge([
                    'title' => Yii::t('app', 'Edit'),
                    'aria-label' => Yii::t('app', 'Edit'),
                    'data-pjax' => '0',
                ], $this->buttonOptions);
                $text = Html::iconCls('fa fa-fw fa-pencil');
                $text .= $this->type == self::ACTION_DROPDOWN ? Yii::t('app', 'Edit') : '';
                return Html::a($text, $url, $options);
            };
        }

        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key) {
                $options = array_merge([
                    'title' => Yii::t('app', 'Delete'),
                    'aria-label' => Yii::t('app', 'Delete'),
                    'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'data-method' => 'post',
                    'data-pjax' => '0',
                ], $this->buttonOptions);
                $text = Html::iconCls('fa fa-fw fa-trash');
                $text .= $this->type == self::ACTION_DROPDOWN ? Yii::t('app', 'Delete') : '';
                return Html::a($text, $url, $options);
            };
        }

        if (isset($this->buttons['class'])) {
            Html::addCssClass($buttons, $this->buttons['class']); // backup
        }

        $this->buttons = array_merge($this->buttons, $buttons);
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $buttons = $this->formatButtons($this->buttons, $model, $key, $index);

        switch ($this->type) {
            case self::ACTION_LINKS:
            case self::ACTION_BUTTONS:
                Html::addCssClass($this->buttonOptions,
                    'btn btn-'. ($this->type === self::ACTION_LINKS ? 'link' : 'default'));
                $buttons = $this->renderButtons($buttons, $model, $key, $index);
                return Html::tag('div', $buttons, array_merge([
                    'class' => 'btn-group btn-group-'. $this->buttonSize
                ], $this->buttonContainerOptions));
            case self::ACTION_DROPDOWN:
                return $this->buildButtonDropdown($buttons, $model, $key, $index);
            case self::ACTION_DEFAULT:
            default:
                return $this->renderButtons($buttons, $model, $key, $index);
        }
    }

    /**
     * Render the all button in cell content
     * @param mixed $model the data model
     * @param mixed $key this key associated with the data model
     * @param integer $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    protected function renderButtons($buttons, $model, $key, $index)
    {
        $result = [];
        foreach ($buttons as $name => $options) {
            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible) {
                if ($options instanceof \Closure) {
                    $url = $this->createUrl($name, $model, $key, $index);
                    $button = call_user_func($buttons[$name], $url, $model, $key);
                    if ($button) {
                        $result[] = $button;
                    }
                } elseif (is_array($options)) {
                    $encodeLabel = true;
                    if (isset($options['encodeLabel']) && is_bool($options['encodeLabel'])) {
                        $encodeLabel = $options['encodeLabel'];
                    }

                    $label = '';
                    if (isset($options['label'])) {
                        $label = $options['label'];
                        if ($encodeLabel === true) {
                            $label = Html::encode($label);
                        }
                    }

                    $url = '#';
                    if (isset($options['url'])) {
                        $url = $options['url'];
                    }

                    if (!isset($options['options'])) {
                        $options['options'] = [];
                    }

                    if (!isset($options['type'])) {
                        $options['type'] = 'button';
                    }

                    if (isset($options['options']['class'])) {
                        Html::addCssClass($this->buttonOptions, $options['options']['class']);
                    }

                    if (isset($options['items'])) {
                        $result[] = ButtonDropdown::widget([
                            'label' => $label,
                            'options' => Html::mergeAttribute([
                                'class' => 'btn btn-default btn-'. $this->buttonSize,
                                'type' => 'button'
                            ], $this->buttonOptions),
                            'encodeLabel' => $encodeLabel,
                            'dropdown' => [
                                'items' => $options['items']
                            ]
                        ]);
                    } else {
                        $options['options'] = array_merge($options['options'], $this->buttonOptions);
                        $button = Html::a($label, $url, $options['options']);
                        if ($button) {
                            $result[] = $button;
                        }
                    }
                }
            }
        }

        return implode('', $result);
    }

    /**
     * Render the button dropdown in cell content
     * @param mixed $model the data model
     * @param mixed $key this key associated with the data model
     * @param integer $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    protected function buildButtonDropdown($buttons, $model, $key, $index)
    {
        $items = [];
        foreach ($buttons as $name => $options) {
            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible) {
                if ($options instanceof \Closure) {
                    $url = $this->createUrl($name, $model, $key, $index);
                    $button = call_user_func($buttons[$name], $url, $model, $key);
                    if ($button) {
                        $items[] = Html::tag('li', $button);
                    }
                } else {
                    $items[] = $options;
                }
            }
        }

        return ButtonDropdown::widget([
            'label' => '<i class="'. $this->iconCls .'"></i>',
            'options' => [
                'class' => 'btn btn-default',
                'type' => 'button'
            ],
            'containerOptions' => [
                'class' => 'btn-group-'. $this->buttonSize
            ],
            'encodeLabel' => false,
            'dropdown' => [
                'encodeLabels' => false,
                'items' => $items,
                'submenuOptions' => [
                    'class' => 'dropdown-menu'
                ]
            ]
        ]);
    }

    protected function formatButtons($childs = [], $model, $key, $index)
    {
        $result = [];
        foreach ($childs as $i => $value) {
            if (!is_numeric($i)) {
                $i = trim($i);
            }

            if ($i === 'url' && $value instanceof \Closure) {
                $result[$i] = call_user_func($value, $model, $key, $index);
            } else if ($i === 'items') {
                $result[$i] = $this->formatButtons($value, $model, $key, $index);
            } else if ($i === 'visible') {
                if ($value instanceof \Closure) {
                    $value = call_user_func($value, $model, $key, $index);
                }

                if (is_bool($value) && $value === false) {
                    return '';
                }
            } else if (is_array($value)) {
                $result[$i] = $this->formatButtons($value, $model, $key, $index);
            } else {
                $result[$i] = $value;
            }
        }

        return $result;
    }
}