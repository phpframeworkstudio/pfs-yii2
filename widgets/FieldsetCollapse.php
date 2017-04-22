<?php

namespace pfs\yii\widgets;

use yii\bootstrap\Collapse;
use yii\helpers\ArrayHelper;
use pfs\yii\helpers\Html;
use yii\base\InvalidConfigException;

class FieldsetCollapse extends Collapse
{
    /**
     * @inheritdoc
     */
    public function renderItems()
    {
        $items = [];
        $index = 0;
        foreach ($this->items as $item) {
            if (!array_key_exists('label', $item)) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $header = $item['label'];
            $options = ArrayHelper::getValue($item, 'options', []);
            Html::addCssClass($options, ['widget' => 'fieldset-panel']);
            $items[] = Html::tag('fieldset', $this->renderItem($header, $item, ++$index), $options);
        }

        return implode("\n", $items);
    }

    /**
     * @inheritdoc
     */
    public function renderItem($header, $item, $index)
    {
        if (array_key_exists('content', $item)) {
            $id = $this->options['id'] . '-collapse' . $index;
            $options = ArrayHelper::getValue($item, 'contentOptions', []);
            $options['id'] = $id;
            Html::addCssClass($options, ['widget' => 'panel-collapse', 'collapse' => 'collapse']);

            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            if ($encodeLabel) {
                $header = Html::encode($header);
            }

            $headerToggle = Html::a($header, '#' . $id, [
                    'class' => 'collapse-toggle',
                    'data-toggle' => 'collapse',
                    'data-parent' => '#' . $this->options['id']
                ]) . Html::iconCls('fa fa-fw '. $id .'-icon-item fa-caret-down') . "\n";

            $header = Html::tag('h4', $headerToggle, ['class' => 'panel-title']);

            if (is_string($item['content']) || is_object($item['content'])) {
                $content = Html::tag('div', $item['content'], ['class' => 'panel-body']) . "\n";
            } elseif (is_array($item['content'])) {
                $content = Html::ul($item['content'], [
                    'class' => 'list-group',
                    'itemOptions' => [
                        'class' => 'list-group-item'
                    ],
                    'encode' => false,
                ]) . "\n";
                if (isset($item['footer'])) {
                    $content .= Html::tag('div', $item['footer'], ['class' => 'panel-footer']) . "\n";
                }
            } else {
                throw new InvalidConfigException('The "content" option should be a string, array or object.');
            }

            $clientScript = "  
jQuery('#{$id}').on('show.bs.collapse', function () {
    jQuery('.{$id}-icon-item').addClass('fa-caret-right').removeClass('fa-caret-down');
});
jQuery('#{$id}').on('hide.bs.collapse', function () {
    jQuery('.{$id}-icon-item').addClass('fa-caret-down').removeClass('fa-caret-right');
});";
            $this->view->registerJs(trim($clientScript));
        } else {
            throw new InvalidConfigException('The "content" option is required.');
        }

        $group = [];
        $group[] = Html::tag('legend', $header, ['class' => 'panel-legend']);
        $group[] = Html::tag('div', $content, $options);

        return implode("\n", $group);
    }
}