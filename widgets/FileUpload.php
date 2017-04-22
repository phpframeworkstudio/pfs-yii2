<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use pfs\yii\helpers\Html;
use pfs\yii\web\FileUploadManager;

class FileUpload extends \yii\widgets\InputWidget
{
    /**
     * @var string Label for button browse
     */
    public $buttonLabel = 'Choose...';
    
    /**
     * @var string Icon for button browse
     */
    public $buttonIcon = 'fa fa-folder-open';

    /**
     * @var array Additional attribute for button
     */
    public $buttonOptions = [];

    /**
     * @var array The params informations
     */
    protected $params;

    /** 
     * @var array The options for javascript
     */
    public $clientOptions = [];

    /**
     * @var string The mime of file
     */
    public $mime = 'image/jpg';

    /**
     * @var int Index of file
     */
    public $index = 1;

    /**
     * @var string Action upload url
     */
    public $actionUrl;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->actionUrl === null) {
            $this->actionUrl = Yii::$app->controller->id .'/do-upload';
        }

        if ($this->model->isNewRecord === false) {
            $value = ArrayHelper::getValue($this->model, $this->attribute);
            Yii::$app->fileUploadManager->editFiles(Yii::$app->controller->id,
                $this->attribute, $this->index, $value, $this->mime);

            $this->model->{$this->attribute} = null;
        }

        if (($params = Yii::$app->config->getValue(Yii::$app->controller->id .'.columns.'. $this->attribute)) !== null) {
            $this->params = $params;
            $this->clientOptions = array_merge([
                'attribute' => $this->attribute,
                'model' => $this->model->formName(),
                'fileMaxLength' => ArrayHelper::getValue($this->params, 'length'),
                'fileAllowedTypes' => ArrayHelper::getValue($this->params, 'file-allowed-types'),
                'fileMaxSize' => ArrayHelper::getValue($this->params, 'file-max-size'),
                'fileMinSize' => ArrayHelper::getValue($this->params, 'file-min-size'),
                'fileMaxCount' => ArrayHelper::getValue($this->params, 'file-max-count'),
                'fileMinCount' => ArrayHelper::getValue($this->params, 'file-min-count'),
                'separator' => ArrayHelper::getValue($this->params, 'multiple-separator'),
                'url' => Url::toRoute(is_array($this->actionUrl) ? $this->actionUrl : [$this->actionUrl])
            ], $this->clientOptions);
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = Html::getInputId($this->model, $this->attribute); //$this->getId();
        }

        $this->registerClientScript();
    }

    /** 
     * @inheritdoc
     */
    public function run()
    {
        $result = [];

        $result[] = Html::beginTag('div', ['class' => 'fileupload', 'style' => 'position: relative']);
        $result[] = Html::beginTag('span', Html::mergeAttribute([
            'title' => $this->buttonLabel, 
            'class' => 'toggle-tooltip btn btn-success fileinput-button min-width-100',
        ], $this->buttonOptions));

        // button
        $result[] = Html::tag('span', '', ['class' => $this->buttonIcon]);
        $result[] = ' '. Html::tag('span', $this->buttonLabel);

        // Input
        // $result[] = Html::beginTag('div', [
        //     'class' => 'file-input-wrapper',
        //     'style' => 'position: absolute'
        // ]);
        $result[] = Html::fileInput(null, null, [
            'class' => 'btn btn-default file-input',
            'style' => 'opacity: 0'
        ]);
        // $result[] = Html::endTag('div');
        $result[] = Html::activeHiddenInput($this->model, $this->attribute, Html::mergeAttribute([
            'class' => 'file-value'
        ], $this->options));
        $result[] = Html::endTag('span');

        // Preview
        $result[] = Html::beginTag('table', ['class' => 'table table-condensed table-hover table-fileupload files-container']);
        $result[] = Html::tag('tbody', '', ['class' => 'files']);
        $result[] = Html::endTag('table');
        $result[] = Html::endTag('div');

        return implode("\n", $result);
    }

    /**
     * Register to needed javascript
     */
    public function registerClientScript()
    {
        $view = $this->view;
        FileUploadAsset::register($view);
        $view->registerJs("jQuery(\"#{$this->options['id']}\").pfsFileUpload(". Json::encode($this->clientOptions) .");");
    }
}