<?php

namespace Wapistrano\CoreBundle\Menu;

class BreadCrumb
{
    private $breadCrumb = array();
    private $breadCrumbAction = array();

    public function __construct()
    {
    }

    public function addChild(array $child) {
        $this->breadCrumb += $child;
    }

    public function getBreadCrumb() {

        return $this->breadCrumb;

    }

    public function getBreadCrumbAction() {

        return $this->breadCrumbAction;

    }

    public function setBreadCrumbAction(array $breadCrumbAction) {
        $this->breadCrumbAction = $breadCrumbAction;

    }


}
