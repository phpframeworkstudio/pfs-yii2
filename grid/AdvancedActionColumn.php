<?php

namespace pfs\yii\grid;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonGroup;
use yii\bootstrap\ButtonDropdown;
use pfs\yii\helpers\Html;

class AdvancedActionColumn extends ActionColumn
{
	const ACTION_DEFAULT = 0;
	const ACTION_LINKS = 1;
	const ACTION_GROUP = 2;
	const ACTION_DROPDOWN = 3;
	const ACTION_SUBMIT = 4;

	/**
	 * @var string Icon bars button
	 */
	public $barsIcon = 'fa fa-fw fa-bars';

	/**
	 * @var string Button size
	 */
	public $buttonSize = 'sm';

	/**
	 * @var boolean Enable bootstrap tooltip
	 */
	public $withTooltip = true;

	/**
	 * @var array Dropdown options
	 */
	public $dropdownOptions = [
		'class' => 'btn-default'
	];

	/**
	 * @inheritdoc
	 */
	public $buttonOptions = [];

	/**
	 * @var array Group Options
	 */
	public $groupOptions = [];

	/**
	 * @var int Type of Action Column
	 */
	public $actionType = 3;

	/**
	 * @var string Form inline param name
	 */
	public $inputName = 'form-inline';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$buttons = $this->buttons;
		$this->buttons = [];
		if (!isset($this->buttons['view'])) {
			$this->buttons['view'] = [
				'label' => Yii::t('app', 'Detail'),
				'linkOptions' => [
					'title' => Yii::t('app', 'Detail'),
					'aria-label' => Yii::t('app', 'Detail'),
					'data-pjax' => '0'
				],
				'iconCls' => 'fa fa-fw fa-eye'
			];
		}

		if (!isset($this->buttons['update'])) {
			$this->buttons['update'] = [
				'label' => Yii::t('app', 'Edit'),
				'linkOptions' => [
					'title' => Yii::t('app', 'Edit'),
					'aria-label' => Yii::t('app', 'Edit'),
					'data-pjax' => '0'
				],
				'iconCls' => 'fa fa-fw fa-pencil'
			];
		}

		if (!isset($this->buttons['delete'])) {
			$this->buttons['delete'] = [
				'label' => Yii::t('app', 'Delete'),
				'linkOptions' => [
					'title' => Yii::t('app', 'Delete'),
					'aria-label' => Yii::t('app', 'Delete'),
					'data-pjax' => '0',
					'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'data-method' => 'post'		
				],
				'iconCls' => 'fa fa-fw fa-trash'
			];
		}

		$this->buttons = array_merge_recursive($this->buttons, $buttons);

		$headerOptions = $this->headerOptions;
		$width = ($this->buttonSize == 'lg' ? 120 : 
			($this->buttonSize == 'md' ? 100 : 
				($this->buttonSize == 'sm' ? 80 : 120)
			)
		);

		if ($this->actionType == 2) {
			$width = ($this->buttonSize == 'lg' ? 70 : 
				($this->buttonSize == 'md' ? 60 : 
					($this->buttonSize == 'sm' ? 50 : 70)
				)
			);

			$count = count($this->buttons);
			$width = $width * $count;
		} elseif ($this->actionType == 1) {
			$width = ($this->buttonSize == 'lg' ? 50 : 
				($this->buttonSize == 'md' ? 40 : 
					($this->buttonSize == 'sm' ? 30 : 50)
				)
			);

			$count = count($this->buttons);
			$width = $width * $count;
		}

		$this->headerOptions = Html::mergeAttributes([
			'style' => [
				'width' => $width .'px',
				'min-width' => $width .'px',
				'max-width' => $width .'px',
			]
		], $headerOptions);

		$contentOptions = $this->contentOptions;
		$this->contentOptions = Html::mergeAttributes([
			'class' => 'text-center'
		], $contentOptions);
	}

	/**
	 * @inheritdoc
	 */
	protected function initDefaultButtons()
	{
	}

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index) 
	{
		$isInputMode = Yii::$app->request->get('grid-mode', null) == $this->inputName;

        if (is_array($key)) {
            $isFormInput = true;
            foreach ($key as $name => $value) {
                if (Yii::$app->request->get($name, null) != $value) {
                    $isFormInput = false;
                    break;
                }
            }
        } else {
            $isFormInput = Yii::$app->request->get('id', null) == $key;
        }

		$buttons = $this->formatButtons($model, $key, $index, $this->buttons);
		if ($this->actionType == self::ACTION_SUBMIT || ($isInputMode && $isFormInput)) {
			return $this->renderSubmit($model, $key, $index);
		} else if ($this->actionType == self::ACTION_DROPDOWN) {
			return $this->renderDropdown($buttons);
		} else {
			return $this->renderGroup($buttons, $model, $key, $index);
		}
	}

	/**
	 * Format buttons
	 *
	 * @param object $model
	 * @param mixed|array $key
	 * @param int $index
	 * @param array $buttons
	 * @return array
	 */
	protected function formatButtons($model, $key, $index, $buttons)
	{
		$results = [];
		foreach ($buttons as $name => $item) {
			$isVisible = true;
			if (isset($this->visibleButtons[$name])) {
				if ($this->visibleButtons[$name] instanceof \Closure) {
					$isVisible = call_user_func($this->visibleButtons[$name], $model, $key, $index);
				} else {
					$isVisible = $this->visibleButtons[$name] === true;
				}
			}

			if ($isVisible) {
				$result = [];
				$result['encodeLabel'] = false;
				$result['options'] = [];
				$result['linkOptions'] = [];

				if (isset($item['url'])) {
					if ($item['url'] instanceof \Closure) {
						$result['url'] = call_user_func($item['url'], $model, $key, $index, $this, $this->getArrayKeys($model, $key), Yii::$app->request->queryParams);
					} else if (!empty($item['url'])) {
						$result['url'] = $item['url'];
					} else {

					}
				} else {
					$result['url'] = $this->createUrl($name, $model, $key, $index);
				}

				if (isset($item['label'])) {
					$result['label'] = $item['label'];
				} else {
					$result['label'] = $name;
				}

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

				// Childs
				if (isset($item['items'])) {
					$result['items'] = $this->formatButtons($model, $key, $index, $item['items'], true);
				}

				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Redering button dropdown
	 *
	 * @param array $buttons
	 * @return string
	 */
	protected function renderDropdown($buttons)
	{
		$clear = function($buttons) use (&$clear) {
			$results = [];
			foreach ($buttons as $button) {
				$button['linkOptions'] = Html::mergeAttributes($button['linkOptions'], $this->buttonOptions);

				if (isset($button['iconCls'])) {
					ArrayHelper::remove($button, 'iconCls');
				}
				if (isset($button['items'])) {
					$button['items'] = $clear($button['items']);
				}
				
				if (isset($button['linkOptions']['title'])) {
					unset($button['linkOptions']['title']);
				}
				
				if (isset($button['options']['title'])) {
					unset($button['options']['title']);
				}

				$results[] = $button;
			}
			return $results;
		};

		$this->dropdownOptions = Html::mergeAttributes($this->dropdownOptions, [
			'title' => Yii::t('app', 'Action'),
			'type' => 'button'
		]);

		if ($this->withTooltip) {
			Html::addCssClass($this->dropdownOptions, 'toggle-tooltip');
		}

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

	/**
	 * Redering group buttons
	 *
	 * @param array $buttons
	 * @return string
	 */
	protected function renderGroup($buttons)
	{
		$clear = function($buttons, $isChild = false) use(&$clear) {
			$results = [];

			foreach ($buttons as $button) {
				if ($isChild === false) {

					$button['linkOptions'] = Html::mergeAttributes($button['linkOptions'], $this->buttonOptions);
					Html::addCssClass($button['linkOptions'], 'btn-'. $this->buttonSize);

					if ($this->actionType === self::ACTION_LINKS) {
						Html::addCssClass($button['linkOptions'], 'btn-link');
						Html::addCssStyle($button['linkOptions'], [
							'padding-left' => '2px',
							'padding-right' => '2px'
						], true);
					}

					if ($this->withTooltip) {
						Html::addCssClass($button['linkOptions'], 'toggle-tooltip');
					}

					if (isset($button['url'])) {
						$button['options']['href'] = $button['url'];
						ArrayHelper::remove($button, 'url');
					}
					$button['tagName'] = 'a';
					
					$button['options'] = Html::mergeAttributes($button['options'], $button['linkOptions']);
					ArrayHelper::remove($button, 'linkOptions');

					if (isset($button['iconCls']) && !isset($button['items'])) {
						$button['label'] = Html::iconCls($button['iconCls']);
						ArrayHelper::remove($button, 'iconCls');
					}

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
			'buttons' => $buttons
		]);
	}

	/**
	 * Redering submit button
	 *
	 * @param array $buttons
	 * @return string
	 */
	protected function renderSubmit($model, $key, $index)
	{
        $cancelUrl = Yii::$app->request->queryParams;
        $cancelUrl = array_merge($cancelUrl, (is_array($key) ? $key : ['id' => (string) $key]));
        $cancelUrl[0] = 'index';
        ArrayHelper::remove($cancelUrl, 'grid-mode');

		return ButtonGroup::widget([
            'encodeLabels' => false,
            'options' => [
            	'class' => 'btn-group-'. $this->buttonSize
            ],
            'buttons' => [
                [
                    'label' => Html::tag('i', '', ['class' => 'fa fa-fw fa-check']),
                    'tagName' => 'button',
                    'options' => Html::mergeAttributes([
                        'class' => 'btn btn-default',
                        'type' => 'submit'
                    ], $this->buttonOptions)
                ],
                [
                    'label' => Html::tag('i', '', ['class' => 'fa fa-fw fa-close']),
                    'tagName' => 'a',
                    'options' => Html::mergeAttributes([
                        'class' => 'btn btn-default',
	                    'href' => Url::to($cancelUrl),
	                    'data-pjax' => '0',
                    ], $this->buttonOptions)
                ]
            ]
        ]);
	}

	/**
	 * Redering button dropdown
	 *
	 * @param object $model
	 * @param array|mixed $key
	 * @return array
	 */
	protected function getArrayKeys($model, $key)
	{
		return is_array($key) ? $key : ['id' => (string) $key];
	}
}