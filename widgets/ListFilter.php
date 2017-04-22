<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use pfs\yii\helpers\Html;

class ListFilter extends \yii\base\Widget
{
    public $filterModel;
    public $attribute;
    public $isBetween = false;

    public function init()
    {
        if ($this->filterModel === null) {
            throw new InvalidConfigException('The "filterModel" property failed.');
        }

        if ($this->attribute === null) {
            throw new InvalidConfigException('The "attribute" property failed.');
        }
    }

    public function run()
    {
        $params = [];
        $params[] = '/'. ltrim(Yii::$app->request->pathInfo, '/');
        foreach (Yii::$app->request->queryParams as $key => $value) {
            if (is_array($value) && $key === $this->filterModel->formName()) {
                // other
            } else {
                $params[$key] = $value;
            }
        }
        
        ob_start();
        $form = ActiveForm::begin([
            'method' => 'get',
            'action' => Url::to($params)
        ]);

        echo Html::beginTag('div', [
            'class' => 'input-group input-group-sm',
            'style' => [
                'width' => ($this->isBetween ? '350px' : '200px')
            ]
        ]);

        if ($this->isBetween) {
            echo Html::activeTextInput($this->filterModel, $this->attribute.'[0]', [
                'class' => 'form-control',
                'autocomplete' => 'off'
            ]);
            echo Html::tag('span', '-', [
                'class' => 'input-group-addon'
            ]);
            echo Html::activeTextInput($this->filterModel, $this->attribute.'[1]', [
                'class' => 'form-control',
                'autocomplete' => 'off'
            ]);
        } else {
            echo Html::activeTextInput($this->filterModel, $this->attribute, [
                'class' => 'form-control',
                'autocomplete' => 'off',
                'placeholder' => Yii::t('app', 'Search')
            ]);
        }

        echo Html::beginTag('div', ['class' => 'input-group-btn']);
        echo Html::button(Html::iconCls('fa fa-fw fa-search'), [
            'class' => 'btn btn-default',
            'type' => 'submit'
        ]);
        echo Html::endTag('div');

        echo Html::endTag('div');
        ActiveForm::end();
        return ob_get_clean();
    }
}