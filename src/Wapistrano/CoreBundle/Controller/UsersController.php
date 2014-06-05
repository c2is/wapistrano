<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Users;
use Wapistrano\CoreBundle\Form\UsersTypeAdd;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("/users")
 * @Breadcrumb("Users", routeName="usersList")
 */
class UsersController extends Controller
{

    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('projectsAdd');
        }
        return $this->sectionAction;
    }


    /**
     * @Route("/", name="usersList")
     * @Template("WapistranoCoreBundle::users_list.html.twig")
     */
    public function listAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $users = $em->getRepository('WapistranoCoreBundle:Users')->findBy(array(), array("login" => "ASC"));

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array( 'barTitle' =>  'Users list', 'sectionAction' => $this->getSectionAction(), 'users'=>$users, "flashMessage" => $flashMessage);
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
     * @Route("/{id}/edit", name="usersEdit")
     * @Breadcrumb("{user.login}", routeName="usersEdit", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle:Form:users_update.html.twig")
     */
    public function updateAction(Request $request, Users $user)
    {
        $securityContext = $this->container->get('security.context');
        $twigVars = array();

        $userType = new UsersTypeAdd();

        $form = $this->get('form.factory')->create($userType, $user);
        $form->add('saveTop', 'submit');

        if( $securityContext->isGranted("ROLE_ADMIN") ){
            $form->add('project', 'entity', array(
                'class'   => "WapistranoCoreBundle:Projects",
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name', 'ASC');
                    },
                'property'   => "name",
                'multiple'  => true,
                'expanded'  => true,
            ));

            $options = $form->get('project')->getConfig()->getOptions();
            $choices = $options['choice_list']->getChoices();
            $twigVars["choices"] = $choices;

        }

        $form->add('saveBottom', 'submit', array("label"=>"Save", "attr" => array("class" => "btn btn-default btn-sm")));


        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $user->setCreatedAt($today);
            $user = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'User '.$user->getLogin().' updated');
            return $this->redirect($this->generateUrl('usersList'));
        }

        $twigVars["barTitle"] = "Edit".$user->getLogin();
        $twigVars["sectionAction"] = $this->getSectionAction();
        $twigVars["form"] = $form->createView();


        return $twigVars;

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
}
