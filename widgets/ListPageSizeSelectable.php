<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

class ListPageSizeSelectable extends Widget
{
    /**
     * @var string set the current url
     */
    public $currentUrl = null;

    /**
     * @var array selectable pagination size, default 5, 10, 20, 50, and all
     */
    public $selectable = [5, 10, 20, 50, 'ALL'];

    /**
     * @var yii\data\ActiveDataProvider 
     */
    public $dataProvider;

    /**
     * @var array html options for select tag
     */
    public $options = ['class' => 'form-control'];

    /**
     * @var array html options for form tag
     */
    public $formOptions = ['class' => 'max-width-80'];

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        if ($this->currentUrl === null) {
            $this->currentUrl = $this->dataProvider->pagination->createUrl(0);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $name = $this->dataProvider->pagination->pageSizeParam;
        $current = isset($_GET[$name]) ? $_GET[$name] : $this->dataProvider->pagination->getPageSize();
        $totalCount = $this->dataProvider->getTotalCount();
        $items = [];
        foreach ($this->selectable as $key => $value) {
            if (is_string($value)) {
                $items[$totalCount] = Yii::t('app', $value);
            } else {
                $items[$value] = $value;
            }
        }
        
        if (!isset($this->formOptions['data-pjax'])) {
            $this->formOptions['data-pjax'] = true;
        }

        $this->registerClientScript();

        $html = [];
        $html[] = Html::beginForm($this->currentUrl, 'get', $this->formOptions);
        $html[] = Html::dropDownList($name, $current, $items, $this->options);
        $html[] = Html::submitButton('Submit', $options = ['style' => 'display: none']);
        $html[] = Html::endForm();
        return implode('', $html);
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        $view->registerJs("jQuery(document).on('change', '#". $this->options['id'] ."', function(e) {
    jQuery(this).closest('form').submit();
});");
    }
}