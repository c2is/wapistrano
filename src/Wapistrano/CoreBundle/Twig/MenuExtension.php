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
        $this->container     = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'wapi_render_menu_project' => new \Twig_Function_Method($this, 'renderMenuProject', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'seh_facebook';
    }

    /**
     * @see FacebookHelper::initialize()
     */
    public function renderMenuProject($parameters = array(), $name = null)
    {
        $contextBrand = $this->container->get('wapistrano_core.menu');



        return "toto";
    }
}
