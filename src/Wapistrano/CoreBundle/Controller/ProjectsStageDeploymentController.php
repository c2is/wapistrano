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
     * @Route("/{id}/project_stage/{stageId}/deployment/{deploymentId}",
     * requirements={"projectId" = "\d+", "stageId" = "\d+", "deploymentId" = "\d+"}, name="projectsStageDeploymentHome")
     * @ParamConverter("stage", options={"id" = "stageId"})
     * @ParamConverter("deployment", options={"id" = "deploymentId"})
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Breadcrumb("{stage.name}", routeName="projectsStageHome", routeParameters={"id"="{id}", "stageId"="{stageId}"})
     * @Template("WapistranoCoreBundle::projects_stage_deployment_home.html.twig")
     */
    public function stageDeploymentHomeAction(Request $request, Projects $project, Stages $stage, Deployments $deployment)
    {
        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $twigVars = array();
        $stageService = $this->get("wapistrano_core.stage");
        $stageService->setProjectId($project->getId());
        $stageService->setStageId($stage->getId());
        // $roles = $stageService->getRoles();
        // $session = $request->getSession();
        $twigVars['flashMessage'] = "";
        $twigVars['barTitle'] = 'Deploy';
        $twigVars["deployment"] = $deployment;
        $twigVars["project"] = $project;
        $twigVars["stage"] = $stage;
        $twigVars["flashMessage"] = $flashMessage;

        return $twigVars;
    }

    /**
     * @Route("/{id}/project_stage/{stageId}/deployment/{deploymentId}/{jobHandle}",
     * requirements={"projectId" = "\d+", "stageId" = "\d+", "deploymentId" = "\d+"}, name="projectsStageDeploymentDeploy")
     * @ParamConverter("stage", options={"id" = "stageId"})
     * @ParamConverter("deployment", options={"id" = "deploymentId"})
     */
    public function stageDeployAction(Request $request, Projects $project, Stages $stage, Deployments $deployment, $jobHandle) {
        $gmClient = $this->get("wapistrano_core.gearman");
        $jobLog = $gmClient->getLog($jobHandle);
        $em = $this->container->get('doctrine')->getManager();
        $logger = $this->get('logger');

        // regenerate original stage rb file

        if($deployment->getStatus() == "running")
        {
            if (false !== strpos($jobLog, "Wapistrano job ended")) {
                $today = new \DateTime();
                $deployment->setUpdatedAt($today);
                $deployment->setCompletedAt($today);
                $deployment->setLog($jobLog);
                $deployment->setStatus("success");
                $em->persist($deployment);
                $em->flush();

                $gmClient->delRedisLog($jobHandle);

                $logger->info("Job ended on status success: ".$jobLog);

                $status = "success";

            } elseif (false !== strpos($jobLog, "Wapistrano Job failed")) {
                $today = new \DateTime();
                $deployment->setUpdatedAt($today);
                $deployment->setCompletedAt($today);
                $deployment->setLog($jobLog);
                $deployment->setStatus("failed");
                $em->persist($deployment);
                $em->flush();

                $gmClient->delRedisLog($jobHandle);

                $logger->info("Job ended on status failed: ".$jobLog);
                $status = "failed";
            } else {
                $logger->info("executing job:".$jobLog."".$jobHandle);
                $status = "running";
            }
        } else {
            $jobLog = $deployment->getLog();
            $status = $deployment->getStatus();
        }


        return new Response(json_encode(array("status"=>$status, "log" => $jobLog)));
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
        $twigVars = array();
        $stageService = $this->get("wapistrano_core.stage");
        $stageService->setProjectId($project->getId());
        $stageService->setStageId($stage->getId());
        $roles = $stageService->getRoles();

        $session = $request->getSession();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $deploymentType = new DeploymentsTypeAdd();
        $deployment = new Deployments();

        $form = $this->get('form.factory')->create($deploymentType, $deployment);

        $form->get('task')->setData($taskCommand);

        $confPrompted = array();
        foreach($stageService->getConfigurations() as $confName=>$configuration) {
            if($configuration->getPromptOnDeploy()) {
                $confPrompted[$confName] = $configuration;
                $form->add($confName, null, array('mapped' => false, 'attr' => array('class'=>'form-control')));
            }
        }

        $form->add('saveBottom', 'submit', array('attr' => array('class'=>'btn btn-warning btn-sm'), "label" => "Start deployment"));

        $form->handleRequest($request);

        if ($form->isValid()) {
            foreach($confPrompted as $confName => $configurations) {
                $configurations->setValue($form->get($confName)->getData());
            }

            $job = $stageService->publishStage($project->getId(), $stage->getId(), $confPrompted);

            if(! is_object($job)) {
                $session->getFlashBag()->add('notice', $job);
            }elseif("error" == $job->getTerminateStatus()) {
                $session->getFlashBag()->add('notice', "Stage's configurations couldn't be published, deploy aborted");
                $job->delRedisLog($job->getJobHandle());
            } else {
                $today = new \DateTime();
                $deployment->setCreatedAt($today);
                $deployment = $form->getData();
                $deployment->setUserId($user);

                $deployment->setStage($stage);
                $deployment->setStatus("running");

                $twigVars['deployment'] = $deployment;

                $session = $request->getSession();
                $session->getFlashBag()->add('notice', 'Deployment '.$deployment->getTask().' added');

                $job = $stageService->deployStage($deployment->getTask());
                $deployment->setJobHandle($job->getJobHandle());

                // unset prompted vars
                foreach($confPrompted as $confName => $configurations) {
                    $configurations->setValue("");
                }
                $jobUnset = $stageService->publishStage($project->getId(), $stage->getId());

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($deployment);
                $manager->flush();

                $twigVars['jobHandle'] = $job;



                return $this->redirect($this->generateUrl('projectsStageDeploymentHome', array("id" => $project->getId(), "stageId" => $stage->getId(), "deploymentId" => $deployment->getId())));
            }
        }

        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');


        $twigVars['barTitle'] = 'Deploy';
        $twigVars['project'] = $project;
        $twigVars['stage'] = $stage;
        $twigVars['flashMessage'] = $flashMessage;
        $twigVars['form'] = $form->createView();
        $twigVars['roles'] = $roles;



        return $twigVars;
    }


}
