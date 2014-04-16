<?php

namespace Wapistrano\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class StageExtension extends \Twig_Extension
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
            'wapi_render_stage_list' => new \Twig_Function_Method($this, 'renderStageList', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wapi_stage';
    }

    public function renderStageList($parameters = array(), $name = null)
    {
        $stages = $this->container->get('wapistrano_core.stage');
        $stages->setProjectId($parameters["projectId"]);

        $stagesList = $stages->getStageList();

        if (isset($parameters["displayType"]) && $parameters["displayType"] == "right") {
            return $this->container->get("templating")->render("WapistranoCoreBundle:Stage:list_right.html.twig",
                array("stages" => $stagesList, "projectId" => $parameters["projectId"]));
        } else {
            return $this->container->get("templating")->render("WapistranoCoreBundle:Stage:list.html.twig",
                array("stages" => $stagesList, "projectId" => $parameters["projectId"]));
        }


    }

}
