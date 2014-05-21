<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Entity\Deployments;
use Wapistrano\CoreBundle\Form\DeploymentsTypeAdd;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/projects")
 * @Breadcrumb("Projects", routeName="projectsList")
 */
class ProjectsStageDeploymentController extends Controller
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
     * @Route("/{projectId}/project_stage/{stageId}/deployment/{deploymentId}", requirements={"projectId" = "\d+", "stageId" = "\d+", "deploymentId" = "\d+"}, name="projectsStageDeploymentHome")
     * @Template("WapistranoCoreBundle::projects_stage_home.html.twig")
     */
    public function stageDeploymentHomeAction(Request $request, $projectId, $stageId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $project = $em->getRepository('WapistranoCoreBundle:Projects')->findOneBy(array("id" => $projectId));
        $stage = $em->getRepository('WapistranoCoreBundle:Stages')->findOneBy(array("id" => $stageId));

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $newConfigurationUrl = $this->generateUrl('stageConfigurationAdd', array("projectId" =>$projectId, "stageId" => $stageId));
        $newRoleUrl = $this->generateUrl('projectsStageRoleAdd', array("projectId" =>$projectId, "stageId" => $stageId));

        $twigVars = array();
        $twigVars['sectionTitle'] = $this->getSectionTitle();
        $twigVars['sectionAction'] = $this->getSectionAction();
        $twigVars['sectionUrl'] = $this->getSectionUrl();
        $twigVars['subSectionTitle'] = $project->getName();
        $twigVars['subSectionUrl'] = $this->generateUrl('projectsHome', array("id" =>$projectId));

        $twigVars['breadCrumb'] = array($this->getSectionTitle() => $this->getSectionUrl());
        $twigVars['breadCrumbAction'] = array("Add" =>  $this->generateUrl('projectsHome', array("id" =>$projectId)));
        $twigVars['title'] = 'Home';
        $twigVars['project'] = $project;
        $twigVars['stage'] = $stage;
        $twigVars['flashMessage'] = $flashMessage;
        $twigVars['newConfigurationUrl'] = $newConfigurationUrl;
        $twigVars['newRoleUrl'] = $newRoleUrl;

        return $twigVars;
    }

    /**
     * @Route("/{id}/project_stage/{stageId}/deployment/add/{taskCommand}/", requirements={"projectId" = "\d+", "stageId" = "\d+"}, name="projectsStageDeploymentAdd")
     * @ParamConverter("stage", options={"id" = "stageId"})
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"}, routeAbsolute=true)
     * @Template("WapistranoCoreBundle:Form:deployments_create.html.twig")
     */
    public function stageDeploymentAddAction(Request $request, Projects $project,Stages $stage, $taskCommand)
    {

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $deploymentType = new DeploymentsTypeAdd();
        $deployment = new Deployments();


        $form = $this->get('form.factory')->create($deploymentType, $deployment);
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $deployment->setCreatedAt($today);
            $deployment = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($deployment);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Deployment '.$deployment->getTask().' added');

           // return $this->redirect($this->generateUrl('projectsList'));
        }

        $twigVars = array();
        $twigVars['sectionTitle'] = $this->getSectionTitle();
        $twigVars['sectionAction'] = $this->getSectionAction();
        $twigVars['sectionUrl'] = $this->getSectionUrl();
        $twigVars['subSectionTitle'] = $project->getName();
        $twigVars['subSectionUrl'] = $this->generateUrl('projectsHome', array("id" =>$project->getId()));

        $twigVars['title'] = 'Home';
        $twigVars['project'] = $project;
        $twigVars['stage'] = $stage;
        $twigVars['flashMessage'] = $flashMessage;
        $twigVars['form'] = $form->createView();
        return $twigVars;
    }


}
