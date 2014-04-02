<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/projects")
 */
class ProjectsController extends Controller
{
    /**
     * @Route("/", name="projectsHome")
     * @Template("WapistranoCoreBundle::projects_list.html.twig")
     */
    public function indexAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Projects')->findAll();

        foreach ($projects as $project) {

        }
        return array('sectionTitle'=>'Project', 'title'=>'list');
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

            return $this->redirect($this->generateUrl('projectsHome'));
        }
        return array('sectionTitle'=>'Project', 'title'=>'Add', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }
}
