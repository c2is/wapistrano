<?php

namespace Wapistrano\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigurationExtension extends \Twig_Extension
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
            'wapi_render_configuration_list' => new \Twig_Function_Method($this, 'renderConfigurationList', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wapi_configuration';
    }

    public function renderConfigurationList($parameters = array(), $name = null)
    {
        $configurations = $this->container->get('wapistrano_core.configuration');
        $configurations->setProjectId($parameters["projectId"]);

        $configurationsList = $configurations->getConfigurationList();

        return $this->container->get("templating")->render("WapistranoCoreBundle:Configuration:list.html.twig",
            array("configurations" => $configurationsList, "projectId" => $parameters["projectId"]));
    }

}
