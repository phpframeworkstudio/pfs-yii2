<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

class MultipleColumnHandler extends \yii\base\Widget
{
    const METHOD_POST = 'POST';

    const METHOD_GET = 'GET';

    /**
     * @var string Grid View ID
     */
    public $gridId;

    /**
     * @var array Url action
     */
    public $url;

    /**
     * @var string Form method
     */
    public $method = 'POST';

    /** 
     * @var string Empty message
     */
    public $emptyMessage = 'Not selected items.';

    /** 
     * @var string Action message
     */
    public $actionMessage = '';

    /**
     * @var string jQuery selector for button
     */
    public $buttonSelector = '';

    /**
     * @var string jQuery selector for checkbox in column
     */
    public $checkboxSelector = '[name="selection[]"]';

    /**
     * @var string Client event for success
     */
    public $success = '';
    
    /**
     * @var string Client event for error
     */
    public $error = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->url)) {
            throw new InvalidConfigException('The "url" property failed.');
        } else if (is_array($this->url)) {
            $this->url = Url::to($this->url);
        } else if ($this->url instanceof \Closure) {
            $this->url = call_user_func($this->url);
        } else {
            $this->url = Url::to($this->url);
        }

        if (empty($this->success)) {
            $this->success = "window.location.reload();";
        }

        if (empty($this->error)) {
            $this->error = "console.log(e);";
        }

        $this->registerClientScript();
    }

    public function registerClientScript()
    {
        $buttonSelector = implode("\'", explode("'", $this->buttonSelector));
        $checkboxSelector = implode("\'", explode("'", $this->checkboxSelector));
        $js = <<<EOF
$(document).on('click', '{$buttonSelector}', function() {
    var values = jQuery('#{$this->gridId}').yiiGridView('getSelectedRows');
    if (values.length) {
        $.confirm({
            title: $.translate('app', 'Confirm'),
            theme: 'bootstrap',
            content: '{$this->actionMessage}',
            buttons: {
                yes: {
                    text: $.translate('app', 'Yes'),
                    btnClass: 'btn-primary min-width-60',
                    action: function() {
                        $.ajax({
                            url: '{$this->url}',
                            data: {keys: values},
                            dataType: 'json',
                            type: '{$this->method}',
                            success: function(e) {
                                {$this->success}
                            },
                            error: function(e) {
                                {$this->error}
                            },
                            complete: function() {}
                        });
                    }
                },
                no: {
                    text: $.translate('app', 'No'),
                    btnClass: 'btn-default min-width-60',
                    action: function() {}
                }
            }
        });
    } else {
        $.alert({
            title: $.translate('app', 'Alert'),
            theme: 'bootstrap',
            content: '{$this->emptyMessage}',
            buttons: {
                ok: {
                    text: $.translate('app', 'Ok'),
                    btnClass: 'btn-primary min-width-60',
                    action: function() {

                    }
                }
            }
        });
    }
});
EOF;
        Yii::$app->controller->view->registerJs($js);
    }
}