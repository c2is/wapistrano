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
    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('projectsAdd');
        }
        return $this->sectionAction;
    }

    /**
     * @Route("/{id}/project_stage/{stageId}/deployment/{deploymentId}", requirements={"projectId" = "\d+", "stageId" = "\d+", "deploymentId" = "\d+"}, name="projectsStageDeploymentHome")
     * @ParamConverter("stage", options={"id" = "stageId"})
     * @ParamConverter("deplyment", options={"id" = "deploymentId"})
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle::projects_stage_home.html.twig")
     */
    public function stageDeploymentHomeAction(Request $request, Projects $project, Stages $stage)
    {

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $newConfigurationUrl = $this->generateUrl('stageConfigurationAdd', array("projectId" =>$project->getId(), "stageId" => $stage->getId()));
        $newRoleUrl = $this->generateUrl('projectsStageRoleAdd', array("projectId" =>$project->getId(), "stageId" => $stage->getId()));

        $twigVars = array();
        $twigVars['sectionTitle'] = $this->getSectionTitle();
        $twigVars['sectionAction'] = $this->getSectionAction();
        $twigVars['sectionUrl'] = $this->getSectionUrl();
        $twigVars['subSectionTitle'] = $project->getName();
        $twigVars['subSectionUrl'] = $this->generateUrl('projectsHome', array("id" =>$project->getId()));

        $twigVars['breadCrumb'] = array($this->getSectionTitle() => $this->getSectionUrl());
        $twigVars['breadCrumbAction'] = array("Add" =>  $this->generateUrl('projectsHome', array("id" =>$project->getId())));
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
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Breadcrumb("{stage.name}", routeName="projectsStageHome", routeParameters={"id"="{id}", "stageId"="{stageId}"})
     * @Template("WapistranoCoreBundle:Form:deployments_create.html.twig")
     */
    public function stageDeploymentAddAction(Request $request, Projects $project,Stages $stage, $taskCommand)
    {

        $stageService = $this->get("wapistrano_core.stage");
        $stageService->setProjectId($project->getId());
        $stageService->setStageId($stage->getId());
        $roles = $stageService->getRoles();

        $session = $request->getSession();

        $deploymentType = new DeploymentsTypeAdd();
        $deployment = new Deployments();

        $form = $this->get('form.factory')->create($deploymentType, $deployment);

        $form->get('task')->setData($taskCommand);

        $confPrompted = array();
        foreach($stageService->getConfigurations() as $confName=>$configuration) {
            if($configuration->getPromptOnDeploy()) {
                $confPrompted[$confName] = $configuration;
                $form->add($confName, null, array('mapped' => false, 'attr' => array('class'=>'form-control', 'label'=>'Execute')));
            }
        }

        $form->add('saveBottom', 'submit', array('attr' => array('class'=>'btn btn-warning btn-sm')));

        $form->handleRequest($request);

        if ($form->isValid()) {

            if(! $stageService->publishStage($project->getId(), $stage->getId(), $confPrompted)) {
                $session->getFlashBag()->add('notice', "Stage's configurations couldn't be published");
            } else {
                $today = new \DateTime();
                $deployment->setCreatedAt($today);
                $deployment = $form->getData();

                $deployment->setStage($stage);

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($deployment);
                $manager->flush();

                foreach($confPrompted as $confName => $configurations) {
                    $configurations->setValue($form->get($confName)->getData());
                }

                $session = $request->getSession();
                $session->getFlashBag()->add('notice', 'Deployment '.$deployment->getTask().' added');
            }


            // $stageService->deployStage($taskCommand);

           // return $this->redirect($this->generateUrl('projectsList'));
        }

        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $twigVars = array();
        $twigVars['barTitle'] = 'Deploy';
        $twigVars['project'] = $project;
        $twigVars['stage'] = $stage;
        $twigVars['flashMessage'] = $flashMessage;
        $twigVars['form'] = $form->createView();
        $twigVars['roles'] = $roles;

        return $twigVars;
    }


}
