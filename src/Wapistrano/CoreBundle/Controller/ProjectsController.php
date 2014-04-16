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
class ProjectsController extends Controller
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
     * @Route("/", name="projectsList")
     * @Template("WapistranoCoreBundle::projects_list.html.twig")
     */
    public function listAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Projects')->findAll();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'List', 'projects'=>$projects, "flashMessage" => $flashMessage);
    }

    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, name="projectsHome")
     * @Template("WapistranoCoreBundle::projects_home.html.twig")
     */
    public function indexAction(Request $request, $id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $project = $em->getRepository('WapistranoCoreBundle:Projects')->findOneBy(array("id" => $id));

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $newConfigurationUrl = $this->generateUrl('projectsConfigurationAdd', array("id" =>$id));
        $newStageUrl = $this->generateUrl('projectsStageAdd', array("id" =>$id));

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'Home', 'project'=>$project,
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
        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Add', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/edit", name="projectsEdit")
     * @Template("WapistranoCoreBundle:Form:projects_update.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        $em = $this->container->get('doctrine')->getManager();
        $project = $em->getRepository('WapistranoCoreBundle:Projects')->findOneBy(array("id" => $id));

        $projectType = new ProjectsTypeAdd();


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
            $session->getFlashBag()->add('notice', 'Project '.$project->getName().' updated');

            return $this->redirect($this->generateUrl('projectsList'));
        }

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Update', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/delete", name="projectsDelete")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $project = $em->getRepository('WapistranoCoreBundle:Projects')->findOneBy(array("id" => $id));
        $em->remove($project);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Project '.$project->getName().' deleted');
        return $this->redirect($this->generateUrl('projectsList'));
    }

    /**
     * @Route("/{id}/project_configuration/add", name="projectsConfigurationAdd")
     */
    public function configurationAddAction($id)
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

        return $this->redirect($this->generateUrl('projectsHome', array("id" => $projectId)));
    }

    /**
     * @Route("/{id}/project_configuration/list", name="projectsConfigurationList")
     */
    public function configurationListAction($id)
    {
        $configuration = $this->container->get('wapistrano_core.configuration');
        $configuration->setProjectId($id) ;

        return new Response($this->container->get("templating")->render("WapistranoCoreBundle:Configuration:list.html.twig",
        array("configurations" => $configuration->getConfigurationList(), "projectId" => $id, "isAjax" => true)
        ));
    }

    /**
     * @Route("/{projectId}/project_stage/{stageId}", requirements={"projectId" = "\d+", "stageId" = "\d+"}, name="projectsStageHome")
     * @Template("WapistranoCoreBundle::projects_stage_home.html.twig")
     */
    public function stageHomeAction(Request $request, $projectId, $stageId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $project = $em->getRepository('WapistranoCoreBundle:Projects')->findOneBy(array("id" => $projectId));
        $stage = $em->getRepository('WapistranoCoreBundle:Stages')->findOneBy(array("id" => $stageId));

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        $newConfigurationUrl = $this->generateUrl('projectsConfigurationAdd', array("id" =>$projectId));
        $newStageUrl = $this->generateUrl('projectsStageAdd', array("id" =>$projectId));

        $twigVars = array();
        $twigVars['sectionTitle'] = $this->getSectionTitle();
        $twigVars['sectionAction'] = $this->getSectionAction();
        $twigVars['sectionUrl'] = $this->getSectionUrl();
        $twigVars['subSectionTitle'] = $project->getName();
        $twigVars['subSectionUrl'] = $this->generateUrl('projectsHome', array("id" =>$projectId));
        $twigVars['title'] = 'Home';
        $twigVars['project'] = $project;
        $twigVars['stage'] = $stage;
        $twigVars['flashMessage'] = $flashMessage;
        $twigVars['newConfigurationUrl'] = $newConfigurationUrl;
        $twigVars['newStageUrl'] = $newStageUrl;

        return $twigVars;
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
        $stage->setProjectId($stageId) ;
        $stage->delete($stageId);

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Stage deleted');

        return $this->redirect($this->generateUrl('projectsHome', array("id" => $projectId)));
    }


}
