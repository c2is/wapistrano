<?php

namespace Wapistrano\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuExtension extends \Twig_Extension
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('wapi_render_menu_project', array($this, 'renderMenuProject'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('wapi_render_menu_host', array($this, 'renderMenuHost'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('wapi_render_menu_recipe', array($this, 'renderMenuRecipe'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wapi_menu';
    }

    public function renderMenuProject($parameters = array(), $name = null)
    {
        $projects = $this->container->get('wapistrano_core.menu')->getMenuProjectItems();
        $sectionUrl = $this->container->get("router")->generate('projectsList');
        return $this->container->get("templating")->render("WapistranoCoreBundle:Menu:left_project.html.twig",
            array("sectionUrl" => $sectionUrl, "sectionPict" => "fa fa-dashboard", "menuTitle" =>"Projects", "projects" => $projects));
    }

    public function renderMenuHost($parameters = array(), $name = null)
    {
        $hosts = $this->container->get('wapistrano_core.menu')->getMenuHostItems();
        $sectionUrl = $this->container->get("router")->generate('hostsList');
        return $this->container->get("templating")->render("WapistranoCoreBundle:Menu:left_host.html.twig",
            array("sectionUrl" => $sectionUrl, "sectionPict" => "glyphicon glyphicon-home", "menuTitle" =>"Hosts", "hosts" => $hosts));
    }

    public function renderMenuRecipe($parameters = array(), $name = null)
    {
        $recipes = $this->container->get('wapistrano_core.menu')->getMenuRecipeItems();
        $sectionUrl = $this->container->get("router")->generate('recipesList');
        return $this->container->get("templating")->render("WapistranoCoreBundle:Menu:left_recipe.html.twig",
            array("sectionUrl" => $sectionUrl, "sectionPict" => "glyphicon glyphicon-tasks", "menuTitle" =>"Recipes", "recipes" => $recipes));
    }
}
