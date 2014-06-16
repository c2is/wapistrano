<?php

namespace Wapistrano\CoreBundle\Menu;

use Wapistrano\ProfileBundle\Security\WapistranoUserRights;

class Menu
{
    private $em;
    public function __construct($em, WapistranoUserRights $wapistranoUserRights)
    {
        $this->em = $em;
        $this->wapistranoUserRights = $wapistranoUserRights;
    }

    public function getMenuProjectItems() {

        $allProjects = $this->em->getRepository('WapistranoCoreBundle:Projects')->findBy(array(), array("name" => "ASC"));

        $grantedProject = array();
        foreach($allProjects as $project) {
            if($this->wapistranoUserRights->isProjectGranted($project->getId())) {
                $grantedProject[] = $project;
            }
        }

        return $grantedProject;

    }

    public function getMenuHostItems() {
        $hosts = $this->em->getRepository('WapistranoCoreBundle:Hosts')->findBy(array(), array("name" => "ASC"));

        return $hosts;

    }

    public function getMenuRecipeItems() {
        $recipes = $this->em->getRepository('WapistranoCoreBundle:Recipes')->findBy(array(), array("name" => "ASC"));

        return $recipes;

    }

}
