<?php

namespace Wapistrano\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class DeploymentExtension extends \Twig_Extension
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
            'wapi_render_deployment_list' => new \Twig_Function_Method($this, 'renderDeploymentList', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wapi_deployment';
    }

    public function renderDeploymentList($parameters = array(), $name = null)
    {

        if (! isset($parameters["projectId"])) {
            throw new MissingMandatoryParametersException("projectId");
        }
        $stageService = $this->container->get('wapistrano_core.stage');
        $stageService->setProjectId($parameters["projectId"]);

        if(isset($parameters["stageId"])) {
            $stageService->setStageId($parameters["stageId"]);
        }


        $deployments = $stageService->getDeploymentList();

        return $this->container->get("templating")->render("WapistranoCoreBundle:Stage:deployment_list.html.twig",
            array("deployments" => $deployments, "projectId" => $parameters["projectId"], "stageId" => $parameters["stageId"]));

    }
}
