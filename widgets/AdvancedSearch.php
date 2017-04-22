<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\bootstrap\ActiveForm;
use pfs\yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class AdvancedSearch extends \yii\base\Widget
{
    const TYPE_MODAL = 0;
    const TYPE_COLLAPSE = 1;

    public $searchId;

    /**
     * @var int Advanced Search Type
     */
    public $type = 1;

    /**
     * @var object
     */
    public $model;

    /**
     * @var string
     */
    public $suffixColumn = '_search_field';

    /**
     * @var string
     */
    public $title;

    /**
     * @var boolean 
     */
    public $toggleButton = true;

    /**
     * @var string
     */
    public $toggleId;

    /**
     * @var string
     */
    public $buttonLabel;
    
    /**
     * @var string
     */
    public $buttonClass = 'btn-info';

    /**
     * @var array Button configuration
     */
    public $buttonOptions = [];

    /**
     * @var array
     */
    public $action = ['index'];

    /**
     * @var string
     */
    public $method = 'get';

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * @var array
     * 
     * ```php
     * $columns = [
     *     'title', // textInput
     *     [
     *         'attribute' => 'title2',
     *         'type' => 'select', // text, select, checkbox
     *         'operator' => 'like', // like, not like, =, >=, <=, <>, between, not between
     *         'operator2' => 'like',
     *         'items' => [], // datasource,
     *         'ajax' => [], // datasource,
     *     ]
     * ]
     */
    public $columns = [];

    /**
     * @var array Field types support for search
     */
    private $types = [
        'text',
        'select',
        'checkbox'
    ];

    /**
     * @var array Operator support
     */
    private $operator1 = [
        // 'user' => 'user',
        '=' => '=',
        '<' => '<',
        '>' => '>',
        '<=' => '<=',
        '>=' => '>=',
        '<>' => '<>',
        'like' => 'contain',
        'not like' => 'not contain',
        'start with' => 'start with',
        'end with' => 'end with',
        'between' => 'between',
        'not between' => 'not between'
    ];

    /**
     * @var array Operator support for operator2
     */
    private $operator2 = [
        // 'user' => 'user',
        '=' => '=',
        '<' => '<',
        '>' => '>',
        '<=' => '<=',
        '>=' => '>=',
        '<>' => '<>',
        'like' => 'contain',
        'not like' => 'not contain',
        'start with' => 'start with',
        'end with' => 'end with'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->model === null) {
            throw new InvalidConfigException('The "model" property failed.');
        }

        if (!count($this->columns)) {
            throw new InvalidConfigException('The "columns" property failed.');
        }

        if ($this->searchId === null) {
            throw new InvalidConfigException('The "searchId" property failed.');
        }

        if ($this->title === null) {
            $this->title = Yii::t('app', 'Advanced Search');
        }

        if ($this->buttonLabel === null) {
            $this->buttonLabel = Yii::t('app', 'Search');
        }

        if ($this->toggleButton === false && $this->toggleId === null) {
            throw new InvalidConfigException('The "toggleId" property failed.');
        }

        if (!isset($this->clientOptions['searchId'])) {
            $this->clientOptions['searchId'] = $this->searchId;
        }

        if (!isset($this->clientOptions['isModal'])) {
            $this->clientOptions['isModal'] = $this->type === self::TYPE_MODAL;
        }

        if ($this->toggleId) {
            $this->clientOptions['toggleId'] = $this->toggleId;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->type === self::TYPE_MODAL) {
            $content = $this->buildModalSearch();
        } else if ($this->type === self::TYPE_COLLAPSE) {
            $content = $this->buildCollapseSearch();
        }

        $this->registerClientScript();

        return $content;
    }

    /**
     * Build advanced search form on modal dialog
     * @return string
     */
    protected function buildModalSearch()
    {
        $form = $this->buildForm();
        if (!$form) {
            return false;
        }

        ob_start();
        Modal::begin([
            'id' => $this->getId(),
            'size' => 'modal-lg',
            'header' => '<h4 class="modal-title">'. $this->title .'</h4>',
            'toggleButton' => $this->toggleButton === true 
                ? Html::mergeAttribute([
                    'label' => $this->buttonLabel,
                    'class' => 'btn '. $this->buttonClass
                ], $this->buttonOptions)
                : false
        ]);
        echo $form;
        Modal::end();

        $content = ob_get_clean();
        return $content;
    }

    /**
     * Build advanced search form on collapse
     * @return string
     */
    protected function buildCollapseSearch()
    {
        $form = $this->buildForm();

        if (!$form) {
            return false;
        }

        $result = [];
        if ($this->toggleButton === true) {
            $result[] = Button::widget([
                'id' => $this->getId() .'-button',
                'label' =>  $this->buttonLabel,
                'options' => Html::mergeAttribute([
                    'class' => 'btn '. $this->buttonClass
                ], $this->buttonOptions)
            ]);
        }

        $result[] = Html::beginTag('div', [
            'id' => $this->getId(),
            'class' => 'panel-collapse collapse',
            'style' => [
                'margin' => '15px 0px'
            ]
        ]);
        $result[] = Html::beginTag('div', ['class' => 'panel panel-default']);
        $result[] = Html::tag('div', $this->title, ['class' => 'panel-heading']);
        $result[] = Html::tag('div', $form, ['class' => 'panel-body']);
        $result[] = Html::endTag('div');
        $result[] = Html::endTag('div');

        $content = implode("\n", $result);

        return $content;
    }

    /**
     * Build advanced search form
     * @return string
     */
    protected function buildForm()
    {
        ob_start();
        $form = ActiveForm::begin([
            'action' => Url::to($this->action),
            'method' => $this->method,
            'layout' => 'horizontal'
        ]);
        echo $this->buildColumns($form);
        echo '<hr />';
        echo Html::beginTag('div', ['class' => 'form-group']);
        echo Html::tag('div', '&nbsp;', ['class' => 'col-sm-3']);
        echo Html::beginTag('div', ['class' => 'col-sm-9']);
        echo Button::widget([
            'encodeLabel' => false,
            'label' => Html::iconCls('fa fa-fw fa-search') . Yii::t('app', 'Search'),
            'options' => [
                'class' => 'btn-primary min-width-90 pull-left',
                'type' => 'submit',
            ]
        ]);

        echo Html::beginTag('div', [
            'class' => 'input-group col-sm-5 pull-left',
            'style' => [
                'margin-left' => '5px'
            ]
        ]);
        echo Html::beginTag('span', ['class' => 'input-group-btn']);
        echo Html::button(Html::iconCls('fa fa-fw fa-save') . Yii::t('app', 'Save'), [
            'class' => 'btn btn-info min-width-90',
            'id' => $this->getId() .'-save-button'
        ]);
        echo Html::endTag('span');
        echo Html::textInput($this->getId() .'-save-name', null, [
            'class' => 'form-control'
        ]);
        echo Html::endTag('div');

        echo Html::endTag('div');
        echo Html::endTag('div');
        ActiveForm::end();
        $content = ob_get_clean();

        if (!empty($content)) {
            return $content;
        }

        return false;
    }

    /**
     * Build advanced search form fields
     * @return string
     */
    protected function buildColumns($form)
    {
        $html = [];
        foreach ($this->columns as $column) {
            $result = [];
            if (is_array($column)) {
                if (isset($column['type']) && in_array($column['type'], $this->types) && isset($column['attribute'])) {
                    $type = $column['type'];
                    $attribute = $column['attribute'];
                    $operator1 = isset($column['operator']) ? $column['operator'] : false;
                    $operator2 = isset($column['operator2']) ? $column['operator2'] : false;
                    $items = isset($column['items']) && (is_array($column['items']) || $column['items'] instanceof \Closure) ? $column['items'] : false;
                    $ajax = isset($column['ajax']) && is_array($column['ajax']) ? $column['ajax'] : false;
                    
                    // field a
                    $field_a = $this->buildField($attribute, 0, $type, $items, $ajax, $form, ['class' => 'field-a']);

                    // field b
                    $field_b = $this->buildField($attribute, 3, $type, $items, $ajax, $form, ['class' => 'field-b']);

                    // newline
                    $newLine = '<hr style="margin:6px 0; visibility: hidden" />';

                    $fieldA = Html::getInputId($this->model, $attribute . $this->suffixColumn .'[0]');
                    $operatorA = Html::getInputId($this->model, $attribute . $this->suffixColumn .'[1]');
                    $andOr = Html::getInputId($this->model, $attribute . $this->suffixColumn .'[2]');
                    $fieldB = Html::getInputId($this->model, $attribute . $this->suffixColumn .'[3]');
                    $operatorB = Html::getInputId($this->model, $attribute . $this->suffixColumn .'[4]');

                    if ($field_a === false) {
                        continue;
                    }

                    // 0 -> field_a
                    // 1 -> operator_a
                    // 2 -> and or
                    // 3 -> field_b
                    // 4 -> operator_b

                    $result[] = Html::beginTag('div', ['class' => 'form-group', 'data-search-attribute' => $attribute]);
                    $result[] = Html::activeLabel($this->model, $attribute, [
                        'class' => 'col-sm-3 control-label'
                    ]);
                    $result[] = Html::beginTag('div', ['class' => 'col-sm-9']);

                    if ($operator1 == 'between' || $operator1 == 'not between') {
                        $result[] = $field_a;
                        $result[] = $newLine;
                        $result[] = $field_b;
                        // bypass
                        $operator2 = false;
                    } elseif ($operator1 == 'user') {
                        $result[] = Html::beginTag('div', ['class' => 'row']);
                        $result[] = Html::tag('div', Html::activeDropDownList($this->model, $attribute . $this->suffixColumn .'[1]', $this->operator1, [
                            'class' => 'form-control operator-a',
                        ]), ['class' => 'col-sm-3']);
                        $result[] = Html::tag('div', $field_a, ['class' => 'col-sm-9']);
                        $result[] = Html::endTag('div');

                        // inline js
                        // ---------------------------------------------------
                        $js = "";
                        $js .= "$(document).on('change', '#$operatorA', function() {\n";
                        $js .= "var oprt = $(this).val();\n";
                        
                        if ($operator2 === false) {
                            $js .= "if (oprt == 'between' || oprt == 'not between') {\n";
                            $js .= "$('#{$fieldB}').show();\n";
                            $js .= "} else {\n";
                            $js .= "$('#{$fieldB}').hide()\n";
                            $js .= "}\n";
                        } else {
                            $js .= "if (oprt == 'between' || oprt == 'not between') {\n";
                            $js .= "$('#{$andOr}').hide();\n";
                            $js .= "$('#{$operatorB}').hide();\n";
                            $js .= "} else {\n";
                            $js .= "$('#{$andOr}').show();\n";
                            $js .= "$('#{$operatorB}').show();\n";
                            $js .= "}";
                        }
                        $js .= "});\n";
                        $js .= "$('#{$operatorA}').trigger('change');";
                        $this->view->registerJs(trim($js));
                        // -----------------------------------------------------

                    } else {
                        $result[] = $field_a;
                    }

                    // b
                    if ($operator1 === 'user' && $operator2 == false) {

                        $result[] = $newLine;
                        $result[] = Html::beginTag('div', ['class' => 'row', 'style' => 'margin-top: 0']);
                        $result[] = Html::beginTag('div', ['class' => 'col-sm-offset-3 col-sm-9']);
                        $result[] = $this->buildField($attribute, 3, $type, $items, $ajax, $form, [
                            'class' => 'field-b',
                            'style' => [
                                'display' => 'none'
                            ]
                        ]);
                        $result[] = Html::endTag('div');
                        $result[] = Html::endTag('div');

                    } elseif ($operator2 !== false) {
                        $result[] = $newLine;
                        $result[] = Html::beginTag('div', ['class' => 'row']);
                        $result[] = Html::beginTag('div', ['class' => 'col-sm-3 col-sm-offset-3']);
                        $result[] = Html::activeRadioList($this->model, $attribute . $this->suffixColumn .'[2]', [
                            'and' => 'and',
                            'or' => 'or'
                        ], [
                            'item' => function ($index, $label, $name, $checked, $value) {
                                $options = ['label' => $label, 'value' => $value];
                                return '<div class="radio">' . Html::radio($name, $checked, $options) . '</div>';
                            },
                            'class' => 'and-or'
                        ]);
                        $result[] = Html::endTag('div');
                        $result[] = Html::endTag('div');

                        $result[] = $newLine;
                        $result[] = Html::beginTag('div', ['class' => 'row']);
                        if ($operator2 == 'user') {
                            $operator_b = Html::activeDropDownList($this->model, $attribute . $this->suffixColumn .'[4]', $this->operator2, [
                                'class' => 'form-control operator-b',
                            ]);
                        } else {
                            $operator_b = '';
                        }
                        if ($operator1 == 'user' || $operator2 == 'user') {
                            $result[] = Html::tag('div', $operator_b, ['class' => 'col-sm-3']);
                        }
                        $result[] = Html::tag('div', $field_b, [
                            'class' => $operator1 != 'user' && $operator2 != 'user' ? 'col-sm-12' : 'col-sm-9'
                        ]);
                        $result[] = Html::endTag('div');
                        
                    }
                    $result[] = Html::endTag('div');
                    $result[] = Html::endTag('div');
                }
            } else {
                $result[] = Html::beginTag('div', ['class' => 'form-group', 'data-search-attribute' => $column]);
                $result[] = Html::activeLabel($this->model, $column, [
                    'class' => 'col-sm-3 control-label'
                ]);
                $result[] = Html::tag('div', $this->buildField($column, 0, 'text', [], [], $form, [
                    'class' => 'field-a'
                ]), [
                    'class' => 'col-sm-9'
                ]);
                $result[] = Html::endTag('div');
            }

            if (count($result)) {
                $html[] = implode("\n", $result);
            }
        }

        return implode("\n<hr />\n", $html);
    }

    /**
     * Build advanced search field
     * @return string
     */
    protected function buildField($attribute, $index = 0, $type, $items = [], $ajax = [], $form = null, $options = []) {
        $field = false;
        if ($type === 'text') {
            $field = Html::activeTextInput($this->model, $attribute . $this->suffixColumn .'['. $index .']', Html::mergeAttribute([
                'class' => 'form-control'
            ], $options));
        } else {
            if (is_callable($items) && $items instanceof \Closure) {
                $items = call_user_func($items, $this->model, $form);
            }

            if ($items === false) {
                $items = [];
            } elseif (!isset($items['']) && $type === 'select') {
                $newItems = ['' => ''];
                foreach ($items as $key => $value) {
                    $newItems[$key] = $value;
                }
                $items = $newItems;
            }

            if (!count($items) && $ajax && count($ajax)) {
                if (isset($ajax['depends']) && is_array($ajax['depends'])) {
                    $depends = [];

                    foreach ($ajax['depends'] as $key => $value) {
                        if (is_array($value)) {
                            $depends[$key] = array_merge_recursive($value, [
                                'id' => Html::getInputId($this->model, $key . $this->suffixColumn .'['. $index .']')
                            ]);
                        } else {
                            $depends[$value] = [
                                'id' => Html::getInputId($this->model, $value . $this->suffixColumn .'['. $index .']')
                            ];
                        }
                    }
                    $ajax['depends'] = $depends;
                }

                if ($type === 'select') {
                    $field = AjaxDropDownList::widget(array_merge_recursive([
                        'model' => $this->model,
                        'attribute' => $attribute . $this->suffixColumn .'['. $index .']',
                        'value' => ArrayHelper::getValue(ArrayHelper::getValue($this->model, $attribute . $this->suffixColumn, []), $index, ''),
                        'options' => [
                            'class' => 'form-control'
                        ],
                        'items' => [
                            '' => ''
                        ]
                    ], $ajax));
                } elseif ($type === 'checkbox') {
                    $field = AjaxCheckboxList::widget(array_merge_recursive([
                        'model' => $this->model,
                        'attribute' => $attribute . $this->suffixColumn .'['. $index .']'
                    ], $ajax));
                }
            } else {
                if ($type === 'select') {
                    $field = Html::activeDropDownList($this->model, $attribute . $this->suffixColumn .'['. $index .']', $items, Html::mergeAttribute([
                        'class' => 'form-control'
                    ], $options));
                } elseif ($type === 'checkbox') {
                    $field = Html::activeCheckBoxList($this->model, $attribute . $this->suffixColumn .'['. $index .']', $items, array_merge([
                        'item' => function ($index, $label, $name, $checked, $value) {
                            $options = ['label' => $label, 'value' => $value];
                            return '<div class="checkbox">' . Html::checkbox($name, $checked, $options) . '</div>';
                        },
                    ], $options));
                }
            }
        }

        return $field;
    }

    /**
     * Register to needed javascript
     */
    public function registerClientScript()
    {
        $view = $this->view;
        AdvancedSearchAsset::register($view);
        $view->registerJs("jQuery(\"#". $this->getId() ."\").advancedSearch(". Json::encode($this->clientOptions) .");");
    }
}