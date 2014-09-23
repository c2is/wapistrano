<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Wapistrano\ProfileBundle\Controller\UserRightsController;

/**
 * @Route("/projects")
 * @Breadcrumb("Projects", routeName="projectsList")
 */
class ProjectsController extends Controller implements UserRightsController
{

    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('projectsAdd');
        }
        return $this->sectionAction;
    }


    /**
     * @Route("/", name="projectsList")
     * @Template("WapistranoCoreBundle::projects_list.html.twig")
     */
    public function listAction(Request $request)
    {
	// get project filtered by right access
        $projects = $this->container->get('wapistrano_core.menu')->getMenuProjectItems();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array( 'barTitle' =>  'Projects list', 'sectionAction' => $this->getSectionAction(), 'projects'=>$projects, "flashMessage" => $flashMessage);
    }

    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, name="projectsHome")
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle::projects_home.html.twig")
     */
    public function indexAction(Request $request, Projects $project)
    {

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $newConfigurationUrl = $this->generateUrl('projectsConfigurationAdd', array("id" =>$project->getId()));
        $newStageUrl = $this->generateUrl('projectsStageAdd', array("id" =>$project->getId()));

        return array('barTitle' =>  $project->getName(), 'project'=>$project,
            "flashMessage" => $flashMessage, "newConfigurationUrl" => $newConfigurationUrl, "newStageUrl" => $newStageUrl);
    }

    public function getUrlAction($action, $id = ""){

        if ("" == $id) {
            return new Response($this->generateUrl('projects'.$action));
        } else {
            return new Response($this->generateUrl('projects'.$action, array("id" => $id)));

        }

    }
    /**
     * @Route("/add", name="projectsAdd")
     * @Template("WapistranoCoreBundle:Form:projects_create.html.twig")
     */
    public function addAction(Request $request)
    {
        $projectType = new ProjectsTypeAdd();
        $project = new Projects();


        $form = $this->get('form.factory')->create($projectType, $project);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $project->setCreatedAt($today);
            $project = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($project);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Project '.$project->getName().' added');

            return $this->redirect($this->generateUrl('projectsList'));
        }
        return array('barTitle' =>  'Add new project', 'sectionAction' => $this->getSectionAction(), 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/edit", name="projectsEdit")
     * @Breadcrumb("{project.name}", routeName="projectsHome", routeParameters={"id"="{id}"})
     */
    public function updateAction(Request $request, Projects $project)
    {


        $projectType = new ProjectsTypeAdd();


        $form = $this->get('form.factory')->create($projectType, $project);
        $form->add('saveTop', 'submit');

        if (! $request->isXmlHttpRequest()) {
            $form->add('saveBottom', 'submit');
        }


        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $project->setCreatedAt($today);
            $project = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($project);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Project '.$project->getName().' updated');
            if (! $request->isXmlHttpRequest()) {
                return $this->redirect($this->generateUrl('projectsList'));
            }

        }
        $formUrl = $this->generateUrl('projectsEdit', array("id" => $project->getId()));
        // ajax call
        if ($request->isXmlHttpRequest()) {
            return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Popin:project.html.twig",
                array("popinTitle" => "Edit a project", 'barTitle' =>  "Edit a project", 'sectionAction' => $this->getSectionAction(), 'form' => $form->createView(), 'formUrl' => $formUrl)
            ));
        } else {
            return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Form:projects_update.html.twig",
                array('barTitle' =>  'Edit '.$project->getName(), 'sectionAction' => $this->getSectionAction(),'form' => $form->createView(), 'formUrl' => $formUrl)
            ));
        }

    }

    /**
     * @Route("/{id}/delete", name="projectsDelete")
     */
    public function deleteAction(Request $request, Projects $project)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->remove($project);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Project '.$project->getName().' deleted');
        return $this->redirect($this->generateUrl('projectsList'));
    }

    public function getLastDeployAction($projectId) {
        $em = $this->container->get('doctrine')->getManager();

        $queryBuilder = $em->getRepository('WapistranoCoreBundle:Deployments')
            ->createQueryBuilder('dp');

        $queryBuilder
            ->join("dp.stage", "stage")
            ->where('stage.project = :projectId')
            ->setParameters(
                array('projectId' => sprintf('%s', $projectId)))
            ->orderBy('dp.createdAt', 'DESC');


        $query = $queryBuilder->getQuery();
        $deployments = $query->getResult();
        if(count($deployments) > 0) {
            $resp =  "Stage ".$deployments[0]->getStage()->getName().", task ".$deployments[0]->getTask();
        } else {
            $resp = "Never deployed";
        }
        //var_dump($resp);
        return new Response($resp);

    }
}
