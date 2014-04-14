<?php

namespace Wapistrano\CoreBundle\Menu;

class Menu
{
    private $em;
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getMenuProjectItems() {
        $projects = $this->em->getRepository('WapistranoCoreBundle:Projects')->findAll();

        return $projects;

    }

    public function getMenuHostItems() {
        $hosts = $this->em->getRepository('WapistranoCoreBundle:Hosts')->findAll();

        return $hosts;

    }

    public function getMenuRecipeItems() {
        $recipes = $this->em->getRepository('WapistranoCoreBundle:Recipes')->findAll();

        return $recipes;

    }

}
