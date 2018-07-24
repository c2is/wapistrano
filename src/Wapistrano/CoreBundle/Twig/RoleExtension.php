<?php

namespace Wapistrano\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class RoleExtension extends \Twig_Extension
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
             new \Twig_SimpleFunction('wapi_render_role_list', array($this, 'renderRoleList'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wapi_role';
    }

    public function renderRoleList($parameters = array(), $name = null)
    {

        if (! isset($parameters["projectId"])) {
            throw new MissingMandatoryParametersException("projectId");
        }

        if (! isset($parameters["stageId"])) {
            throw new MissingMandatoryParametersException("stageId");
        }

        $roles = $this->container->get('wapistrano_core.role');
        $roles->setStageId($parameters["stageId"]);

        $rolesList = $roles->getRoleList();

        return $this->container->get("templating")->render("WapistranoCoreBundle:Role:list.html.twig",
                array("roles" => $rolesList, "projectId" => $parameters["projectId"], "stageId" => $parameters["stageId"]));

    }


}
