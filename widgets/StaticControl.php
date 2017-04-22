<?php

namespace pfs\yii\widgets;

use yii\helpers\ArrayHelper;
use pfs\yii\helpers\Html;

class StaticControl extends \yii\widgets\InputWidget
{

    /**
     * @var string Tag name
     */
    public $tag = 'p';

    /*
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'form-control-static');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return Html::tag($this->tag, ArrayHelper::getValue($this->model, $this->attribute), $this->options);
    }
}