<?php

namespace pfs\yii\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonGroup;
use yii\bootstrap\ButtonDropdown;
use pfs\yii\helpers\Html;

class ButtonBar extends \yii\base\Widget
{
	const BAR_DROPDOWN = 0;
	const BAR_GROUP = 1;

	public $barType = 1;
	public $buttons = [];
	public $urlCreator;

	public $barsIcon = 'fa fa-fw fa-bars';

	public $buttonSize = 'md';

	public $dropdownOptions = [];

	public $groupOptions = [];

	public $buttonOptions = [];

	public $iconOnly = false;

	public function init()
	{
		parent::init();
	}

	public function run()
	{
		if (!is_array($this->buttons)) {
			$this->buttons = [];
		}

		$buttons = $this->formatButtons($this->buttons);
		if ($this->barType === self::BAR_DROPDOWN) {
			return $this->renderDropdown($buttons);
		} else {
			return $this->renderGroup($buttons);
		}
	}

	protected function renderDropdown($buttons)
	{
		$clear = function($buttons) use (&$clear) {
			$results = [];
			foreach ($buttons as $button) {
				$button['linkOptions'] = Html::mergeAttribute($button['linkOptions'], $this->buttonOptions);

				if (isset($button['iconCls'])) {
					ArrayHelper::remove($button, 'iconCls');
				}
				if (isset($button['items'])) {
					$button['items'] = $clear($button['items']);
				}
				$results[] = $button;
			}
			return $results;
		};

		$this->dropdownOptions = Html::mergeAttribute($this->dropdownOptions, [
			'class' => 'btn btn-default',
			'type' => 'button'
		]);

		$buttons = $clear($buttons);
		return ButtonDropdown::widget([
			'label' => Html::iconCls($this->barsIcon),
			'options' => $this->dropdownOptions,
			'containerOptions' => [
				'class' => 'btn-group-'. $this->buttonSize
			],
			'encodeLabel' => false,
			'dropdown' => [
				'encodeLabels' => false,
				'items' => $buttons,
                'submenuOptions' => [
                    'class' => 'dropdown-menu'
                ]
			]
		]);
	}

	protected function renderGroup($buttons)
	{
		// Closure
		$clear = function($buttons, $isChild = false) use(&$clear) {
			$results = [];

			foreach ($buttons as $button) {
				if ($isChild === false) {
					$button['linkOptions'] = Html::mergeAttribute($button['linkOptions'], $this->buttonOptions);

					if (isset($button['url'])) {
						$button['options']['href'] = $button['url'];
					}

					$button['tagName'] = 'a';
					
					$button['options'] = Html::mergeAttribute($button['options'], $button['linkOptions']);


					if ($this->iconOnly && isset($button['iconCls']) && !isset($button['items'])) {
						$button['label'] = Html::iconCls($button['iconCls']);
					}

					ArrayHelper::remove($button, 'iconCls');
					ArrayHelper::remove($button, 'linkOptions');
					ArrayHelper::remove($button, 'url');

					if (isset($button['items'])) {
						$button['items'] = $clear($button['items'], true);

						$button = ButtonDropdown::widget([
							'label' => $button['label'],
							'options' => $button['options'],
							'containerOptions' => [],
							'encodeLabel' => false,
							'dropdown' => [
								'encodeLabels' => false,
								'items' => $button['items'],
				                'submenuOptions' => [
				                    'class' => 'dropdown-menu'
				                ]
							]
						]);
					}
				}
				$results[] = $button;
			}

			return $results;
		};

		$buttons =  $clear($buttons);

		return ButtonGroup::widget([
			'encodeLabels' => false,
			'options' => $this->groupOptions,
			'buttons' => $buttons
		]);
	}

	protected function formatButtons($buttons)
	{
		$results = [];
		foreach ($buttons as $name => $item) {
			$result = [];
			$result['encodeLabel'] = false;
			$result['options'] = [];
			$result['linkOptions'] = [];

			if (isset($item['url'])) {
				if (is_callable($item['url']) && $item['url'] instanceof \Closure) {
					$result['url'] = call_user_func($item['url']);
				} else if (!empty($item['url'])) {
					$result['url'] = $item['url'];
				} else {

				}
			} else {
				$result['url'] = $this->createUrl($name);
			}

			$result['label'] = isset($item['label']) ? $item['label'] : $name;

			if (isset($item['iconCls'])) {
				$result['iconCls'] = $item['iconCls'];
				$result['label'] = Html::iconCls($result['iconCls']) . $result['label'];
			}

			if (isset($item['options'])) {
				$result['options'] = $item['options'];
			}

			if (isset($item['linkOptions'])) {
				$result['linkOptions'] = $item['linkOptions'];
			}

			if (isset($item['items'])) {
				$result['items'] = $this->formatButtons($item['items']);
			}

			$isVisible = true;
			if (isset($item['visible'])) {
				if ($item['visible'] instanceof \Closure) {
					$isVisible = call_user_func($item['visible']);
				} else {
					$isVisible = $item['visible'] === true;
				}
			}

			if ($isVisible) {
				$results[] = $result;
			}
		}
		return $results;
	}

    protected function createUrl($action)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $this);
        } else {
        	return Url::to(['/'. Yii::$app->controller->id .'/'. $action]);
        }
    }
}