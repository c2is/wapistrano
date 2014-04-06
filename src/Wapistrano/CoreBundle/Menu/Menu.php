<?php

namespace Wapistrano\CoreBundle\Menu;

class Menu
{
    private $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getMenuProjectItems() {
        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Projects')->findAll();

        return $projects;

    }

    public function getMenuHostItems() {
        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Hosts')->findAll();

        return $projects;

    }

    public function getMenuRecipeItems() {
        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Recipes')->findAll();

        return $projects;

    }

}
