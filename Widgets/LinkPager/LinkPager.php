<?php

namespace App\Widgets\LinkPager;

use \Base;
use \Base\Base\BaseWidget;
use \Base\Base\Request;
use \Base\Base\DB;

class LinkPager extends BaseWidget
{
    public $offset = 0;
    public $limit = 0;

    protected function init()
    {
        if (!isset($this->data->item)) {
            $this->data->item = null;
        }
        if (!isset($this->data->count)) {
            $this->data->count = 0;
        } else {
            $this->data->count = intval($this->data->count);
        }
        if (!isset($this->data->countOnPage)) {
            $this->data->countOnPage = 20;
        } else {
            $this->data->countOnPage = intval($this->data->countOnPage);
        }

        if (!isset($this->data->countLinks)) {
            $this->data->countLinks = 2;
        }
        if (!isset($this->data->params) || !is_array($this->data->params)) {
            $this->data->params = [];
        }

        $this->data->pageCount = (int)ceil($this->data->count / $this->data->
            countOnPage);

        if (!isset($this->data->pageName)) {
            $this->data->pageName = 'page';
        }
        $this->data->pageNow = (int)intval(request()->{$this->data->pageName});
        if (!$this->data->pageNow) {
            $this->data->pageNow = 1;
        }
        if (($this->data->pageNow > $this->data->pageCount && $this->data->pageCount > 0) ||
            $this->data->pageCount == '0' && $this->data->pageNow > 1) {
            redirect_url(routeNow([], ['page']), 301);
            abort();
        }
        if (!($this->data->pageNow >= '1')) {
            $this->data->pageNow = 1;
        }

        $this->offset = (int)(($this->data->pageNow - 1) * $this->data->countOnPage);
        $this->limit = (int)$this->data->countOnPage;

        $this->data->isLinkFirst = true; //
        $this->data->isLinkLast = true; //
        $this->data->isLinkPrev = true; //
        $this->data->isLinkNext = true; //
        $this->data->isLeftSkip = true; //
        $this->data->isRightSkip = true; //
        $this->data->isLinksCenter = true; //

        if ($this->data->pageNow == '1') {
            $this->data->isLinkPrev = false;
        }
        if ($this->data->pageNow == $this->data->pageCount || $this->data->pageCount == 0 || $this->data->pageNow > $this->data->pageCount) {
            $this->data->isLinkNext = false;
        }
        if (!($this->data->pageNow - $this->data->countLinks > 2)) {
            $this->data->isLeftSkip = false;
        }
        if (!($this->data->pageNow + $this->data->countLinks < $this->data->pageCount -
            1)) {
            $this->data->isRightSkip = false;
        }
        if (!($this->data->pageNow - $this->data->countLinks > 1)) {
            $this->data->isLinkFirst = false;
        }
        if (!($this->data->pageNow + $this->data->countLinks < $this->data->pageCount)) {
            $this->data->isLinkLast = false;
        }

        if (!isset(data()->canonical_link) && request()->get->{$this->data->pageName}) {
            data()->canonical_link = routeNow($this->data->params, [$this->data->pageName]);
        }
        if (!isset(data()->prev_link) && $this->data->isLinkPrev) {
            data()->prev_link = routeNow(array_merge($this->data->params, [$this->data->
                pageName => $this->data->pageNow - 1]));
        }
        if (!isset(data()->next_link) && $this->data->isLinkNext) {
            data()->next_link = routeNow(array_merge($this->data->params, [$this->data->
                pageName => $this->data->pageNow + 1]));
        }
    }
}
