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
            $this->sectionUrl = $this->generateUrl('projectsHome');
        }
        return $this->sectionUrl;
    }
    /**
     * @Route("/", name="projectsHome")
     * @Template("WapistranoCoreBundle::projects_list.html.twig")
     */
    public function indexAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Projects')->findAll();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'List', 'projects'=>$projects, "flashMessage" => $flashMessage);
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

            return $this->redirect($this->generateUrl('projectsHome'));
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

            return $this->redirect($this->generateUrl('projectsHome'));
        }
        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Add', 'form' => $form->createView());
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
        $session->getFlashBag()->add('notice', 'Project '.$project->getName().'deleted');
        return $this->redirect($this->generateUrl('projectsHome'));
    }
}
