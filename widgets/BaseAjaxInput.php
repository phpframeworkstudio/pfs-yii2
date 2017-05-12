<?php

namespace pfs\yii\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;
use pfs\yii\helpers\Html;

class BaseAjaxInput extends \yii\widgets\InputWidget
{
    /** 
     * @var array The parents attribute.
     * ```php
     * [
     *     'category',
     *     'employee_id' => [
     *         'required' => true
     *     ]
     * ]
     * ```
     */
    public $depends = [];

    /**
     * @var string Trigger for data-source load. This trigger support options 'load' and 'click' only. Default use 'load'.
     */
    public $trigger = 'load';

    /**
     * @var string Placeholder label.
     */
    public $placeholder = '';

    /** 
     * @var string Laoding text.
     */
    public $loadingText = 'Loading ...';

    /**
     * @var string The url for load data from server.
     */
    public $url = [];

    /**
     * @var mixed The value of attribute.
     */
    public $value = [];

    /**
     * @var string Type of form field
     */
    public $type = '';

    /** 
     * @var array The options for javascript
     */
    public $clientOptions = [];

    /**
     * @var array Depends options, dont change
     */
    protected $dependOptions = [
        'required' => true,
        'trigger' => 'change'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        if (!isset($this->options['id'])) {
            $this->options['id'] = Html::getInputId($this->model, $this->attribute);
        }
    }

    protected function dependsFormatter()
    {
        if (count($this->depends)) {
            $result = [];

            foreach ($this->depends as $attribute => $options) {
                if (is_array($options)) {
                    $id = Html::getInputId($this->model, $attribute);
                    $name = $attribute;
                    $options = array_merge($this->dependOptions, $options);
                    
                    if (isset($options['id'])) {
                        $id = ArrayHelper::remove($options, 'id', $id);
                    }
                } else {
                    $name = $options;
                    $id = Html::getInputId($this->model, $options);
                    $options = $this->dependOptions;
                }

                $result[] = [
                    'id' => $id,
                    'attribute' => $name,
                    'options' => $options
                ];
            }

            return $result;
        }

        return [];
    }

    /**
     * Register to needed javascript
     */
    public function registerClientScript()
    {
        if (!isset($this->clientOptions['url'])) {
            $this->clientOptions['url'] = $this->url;
        }
        $this->clientOptions['url'] = Url::to($this->clientOptions['url']);

        if (!isset($this->clientOptions['trigger'])) {
            $this->clientOptions['trigger'] = $this->trigger;
        }
        
        if (!isset($this->clientOptions['placeholder'])) {
            $this->clientOptions['placeholder'] = $this->placeholder;
            $this->options['placeholder'] = $this->placeholder;
        }
        
        if (!isset($this->clientOptions['loadingText'])) {
            $this->clientOptions['loadingText'] = $this->loadingText;
        }
        
        if (!isset($this->clientOptions['type'])) {
            $this->clientOptions['type'] = $this->type;
        }
        
        if (empty($this->value)) {
            try {
                $this->value = ArrayHelper::getValue($this->model, $this->attribute, '');
            } catch (\Exception $e) {
                $this->value = '';
            }
        }

        if (!isset($this->clientOptions['value'])) {
            $this->clientOptions['value'] = $this->value;
        }

        $this->clientOptions['depends'] = $this->dependsFormatter();
        $this->clientOptions['options'] = $this->options;
        
        if (empty($this->clientOptions['options']['isNewRecord']) && $this->model) {
            $this->clientOptions['options']['isNewRecord'] = $this->model->isNewRecord;
        }

        $view = $this->view;
        AjaxInputAsset::register($view);
        $view->registerJs("jQuery(\"#{$this->options['id']}\").pfsAjaxInput(". Json::encode($this->clientOptions) .");");

        if (count($this->clientOptions['depends'])) {
            $depends = [];
            foreach ($this->clientOptions['depends'] as $depend) {
                $depends[$depend['id']] = true;
            }
            $view->registerJs("jQuery(\"#{$this->options['id']}\").data(\"depends\", ". Json::encode($depends) .");");
        }
    }
}