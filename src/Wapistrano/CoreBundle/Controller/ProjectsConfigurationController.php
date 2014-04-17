<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/projects")
 */
class ProjectsConfigurationController extends Controller
{
    private $sectionTitle;
    private $sectionAction;
    private $sectionUrl;

    public function getSectionTitle() {
        if (null == $this->sectionTitle) {
            $this->sectionTitle = 'Projects';
        }
        return $this->sectionTitle;
    }

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('projectsAdd');
        }
        return $this->sectionAction;
    }

    public function getSectionUrl() {
        if (null == $this->sectionUrl) {
            $this->sectionUrl = $this->generateUrl('projectsList');
        }
        return $this->sectionUrl;
    }

    /**
     * @Route("/{id}/project_configuration/add", name="projectsConfigurationAdd")
     */
    public function projectConfigurationAddAction($id)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($id) ;

        return new Response($configuration->displayFormAdd());
    }

    /**
     * @Route("/{projectId}/project_configuration/{configurationId}/edit", name="projectsConfigurationEdit")
     */
    public function configurationEditAction($projectId, $configurationId)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($projectId) ;
        $configuration->setConfigurationId($configurationId) ;
        return new Response($configuration->displayFormEdit());
    }

    /**
     * @Route("/{projectId}/project_configuration/{configurationId}/delete", name="projectsConfigurationDelete")
     */
    public function configurationDeleteAction(Request $request, $projectId, $configurationId)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($configurationId) ;
        $configuration->delete($configurationId);

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Configuration deleted');

        $urlRedir = $request->headers->get('referer');
//$this->generateUrl('projectsHome', array("id" => $projectId))
        return $this->redirect($urlRedir);
    }

    /**
     * @Route("/{id}/project_configuration/list", name="projectsConfigurationList")
     */
    public function projectConfigurationListAction($id)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($id) ;

        return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Configuration:list.html.twig",
        array("configurations" => $configuration->getProjectConfigurationList(), "projectId" => $id, "isAjax" => true)
        ));
    }

}
