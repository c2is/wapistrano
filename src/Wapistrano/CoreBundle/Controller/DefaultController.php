<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $projectType = new ProjectsTypeAdd();
        $project = new Projects();


        $form = $this->get('form.factory')->create($projectType, $project);
        $form->add('save', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            echo "toto";
            die();

            return $this->redirect($this->generateUrl('task_success'));
        }
        return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }
}
