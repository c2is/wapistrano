<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/projects")
 * @Breadcrumb("Projects", routeName="projectsList")
 */
class ProjectsStageController extends Controller
{
    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('projectsAdd');
        }
        return $this->sectionAction;
    }

    /**
     * @Route("/{id}/project_stage/{stageId}", requirements={"id" = "\d+", "stageId" = "\d+"}, name="projectsStageHome")
     * @ParamConverter("stage", options={"id" = "stageId"})
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Breadcrumb("{stage.name}", routeName="projectsStageHome", routeParameters={"id"="{id}", "stageId"="{stageId}"})
     * @Template("WapistranoCoreBundle::projects_stage_home.html.twig")
     */
    public function stageHomeAction(Request $request, Projects $project, Stages $stage)
    {

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $newConfigurationUrl = $this->generateUrl('stageConfigurationAdd', array("projectId" =>$project->getId(), "stageId" => $stage->getId()));
        $newRoleUrl = $this->generateUrl('projectsStageRoleAdd', array("projectId" =>$project->getId(), "stageId" => $stage->getId()));

        $twigVars = array();
        $twigVars['barTitle'] = $stage->getName();
        $twigVars['project'] = $project;
        $twigVars['stage'] = $stage;
        $twigVars['flashMessage'] = $flashMessage;
        $twigVars['newConfigurationUrl'] = $newConfigurationUrl;
        $twigVars['newRoleUrl'] = $newRoleUrl;
        $twigVars['deploymentActions'] = array("Deploy" => $this->generateUrl('projectsStageDeploymentAdd', array("id" =>$project->getId(), "stageId" => $stage->getId(), "taskCommand" => "deploy")));

        return $twigVars;
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/stage_configuration/add", name="stageConfigurationAdd")
     */
    public function stageConfigurationAddAction($projectId, $stageId)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($projectId);
        $configuration->setStageId($stageId);

        return new Response($configuration->displayFormAdd());
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/stage_configuration/list", name="stageConfigurationList")
     */
    public function stageConfigurationListAction($projectId, $stageId)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($projectId);
        $configuration->setStageId($stageId);

        return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Configuration:list.html.twig",
            array("configurations" => $configuration->getStageConfigurationList(), "projectId" => $projectId, "stageId" => $stageId, "isAjax" => true)
        ));
    }

    /**
     * @Route("/{id}/project_stage/add", name="projectsStageAdd")
     */
    public function stageAddAction($id)
    {
        $stage = $this->container->get('wapistrano_core.stage');
        $stage->setProjectId($id) ;

        return new Response($stage->displayFormAdd());
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/edit", name="projectsStageEdit")
     */
    public function stageEditAction($projectId, $stageId)
    {
        $stage = $this->container->get('wapistrano_core.stage');
        $stage->setProjectId($projectId) ;
        $stage->setStageId($stageId) ;

        return new Response($stage->displayFormEdit());
    }

    /**
     * @Route("/{id}/project_stage/list", name="projectsStageList")
     */
    public function stageListAction($id)
    {
        $stage = $this->container->get('wapistrano_core.stage');
        $stage->setProjectId($id) ;

        return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Stage:list.html.twig",
            array("stages" => $stage->getStageList(), "projectId" => $id, "isAjax" => true)
        ));
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/delete", name="projectsStageDelete")
     */
    public function stageDeleteAction(Request $request, $projectId, $stageId)
    {
        $stage = $this->container->get('wapistrano_core.stage');
        $stage->setProjectId($projectId) ;
        $stage->setStageId($stageId);
        $stage->delete($stageId);

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Stage deleted');

        return $this->redirect($this->generateUrl('projectsHome', array("id" => $projectId)));
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/recipe/manage", name="projectsStageRecipeManage")
     */
    public function stageRecipeManageAction(Request $request, $projectId, $stageId)
    {
        $stage = $this->container->get('wapistrano_core.stage');
        $stage->setProjectId($projectId) ;
        $stage->setStageId($stageId);


        $formReturn = $stage->displayFormRecipeManage();

        if ($formReturn == "redirect") {
            $redirectUrl = $this->generateUrl("projectsStageHome", array("projectId"=>$projectId, "stageId"=>$stageId));
            return $this->redirect($redirectUrl);
        } else {
            return new Response($formReturn);
        }

    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/role_configuration/add", name="projectsStageRoleAdd")
     */
    public function stageRoleAddAction($projectId, $stageId)
    {
        $configuration = $this->container->get('wapistrano_core.role');
        $configuration->setProjectId($projectId);
        $configuration->setStageId($stageId);

        return new Response($configuration->displayFormAdd());
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/role_configuration/{roleId}/edit", name="projectsStageRoleEdit")
     */
    public function stageRoleEditAction($projectId, $stageId, $roleId)
    {
        $role = $this->container->get('wapistrano_core.role');
        $role->setProjectId($projectId) ;
        $role->setStageId($stageId);
        $role->setRoleId($roleId);

        return new Response($role->displayFormEdit());
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/role_configuration/{roleId}/delete", name="projectsStageRoleDelete")
     */
    public function stageRoleDeleteAction(Request $request, $projectId, $stageId, $roleId)
    {
        $role = $this->container->get('wapistrano_core.role');
        $role->setProjectId($projectId) ;
        $role->setStageId($stageId);
        $role->delete($roleId);

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Role deleted');

        return $this->redirect($this->generateUrl('projectsStageHome', array("projectId" => $projectId, "stageId" => $stageId)));
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}/role_configuration/list", name="projectsStageRoleList")
     */
    public function stageRoleListAction($projectId, $stageId)
    {
        $role = $this->container->get('wapistrano_core.role');
        $role->setProjectId($projectId);
        $role->setStageId($stageId);

        return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Role:list.html.twig",
            array("roles" => $role->getRoleList(), "projectId" => $projectId, "stageId" => $stageId, "isAjax" => true)
        ));
    }


}
