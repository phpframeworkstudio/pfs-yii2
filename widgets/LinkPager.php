<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\data\Pagination;
use yii\widgets\LinkPager as Base;
use pfs\yii\helpers\Html;

class LinkPager extends Base
{
    const PAGER_NUMERIC = 0;
    const PAGER_NEXTPREV = 1;
    const PAGER_NEXTPREVINPUT = 2;

    /**
     * @var integer set pager style.
     */
    public $type = 0;

    /**
     * @inheritdoc
     */
    public $nextPageLabel = false;
    
    /**
     * @inheritdoc
     */
    public $prevPageLabel = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->nextPageLabel === false) {
            $this->nextPageLabel = Html::iconCls('fa fa-angle-right');
        }
        
        if ($this->prevPageLabel === false) {
            $this->prevPageLabel = Html::iconCls('fa fa-angle-left');
        }
        
        if ($this->lastPageLabel === false) {
            $this->lastPageLabel = Html::iconCls('fa fa-angle-double-right');
        }
        
        if ($this->firstPageLabel === false) {
            $this->firstPageLabel = Html::iconCls('fa fa-angle-double-left');
        }

        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }

        switch ($this->type) {
            case self::PAGER_NEXTPREV:
                return $this->renderPageNextPrev();
                break;
            case self::PAGER_NEXTPREVINPUT:
                return $this->renderPageNextPrevInput();
                break;            
            case self::PAGER_NUMERIC:
            default:
                return $this->renderPageButtons();
                break;
        }
    }

    /**
     * Renders the page buttons with next-prev style.
     * @return string the rendering result
     */
    protected function renderPageNextPrev()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }

    /**
     * Renders the page buttons with next-input-prev style.
     * @return string the rendering result
     */
    protected function renderPageNextPrevInput()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        $currentPage = $this->pagination->getPage();

        $buttonLeft = [];
        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttonLeft[] = $this->renderButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttonLeft[] = $this->renderButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }
        $buttonLeft = Html::tag('div', implode('', $buttonLeft), ['class' => 'input-group-btn']);
        
        $content = Html::textInput($this->pagination->pageParam, $currentPage + 1, [
            'class' => 'form-control',
            'autocomplete' => 'off'
        ]);
        $content .= Html::submitButton('Submit', $options = ['style' => 'display: none']);

        $buttonRight = [];
        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttonRight[] = $this->renderButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttonRight[] = $this->renderButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        $buttonRight = Html::tag('div', implode('', $buttonRight), ['class' => 'input-group-btn']);

        Html::addCssClass($this->options, 'input-group');
        Html::removeCssClass($this->options, 'pagination');
        Html::addCssClass($this->options, 'margin-t-sm margin-b-sm');

        $currentUrl = $this->pagination->getLinks();
        if (isset($currentUrl['self'])) {
            $currentUrl = $currentUrl['self'];
        } else {
            $currentUrl = Yii::$app->request->url;
        }

        $html = [];
        $html[] = Html::beginForm($currentUrl, 'get', ['class' => 'pagination-input']);
        $html[] = Html::tag('div', $buttonLeft.$content.$buttonRight, $this->options);
        $html[] = Html::endForm();
        return implode("\n", $html);
    }

    /**
     * Renders a button.
     * @param string $label the text label for the button
     * @param integer $page the page number
     * @param string $class the CSS class for the page button.
     * @param boolean $disabled whether this page button is disabled
     * @param boolean $active whether this page button is active
     * @return string the rendering result
     */
    protected function renderButton($label, $page, $class, $disabled, $active)
    {
        $options = ['class' => empty($class) ? $this->pageCssClass : $class];
        Html::addCssClass($options, 'btn btn-primary min-width-40');

        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);
            $options['type'] = 'button';
            return Html::tag('button', Html::tag('span', $label), $options);
        }

        $linkOptions = $this->linkOptions;
        $linkOptions['data-page'] = $page;
        if (isset($linkOptions['class'])) {
            Html::addCssClass($options, $linkOptions['class']);
        }
        $linkOptions = array_merge($linkOptions, $options);
        return Html::a($label, $this->pagination->createUrl($page), $linkOptions);
    }
}