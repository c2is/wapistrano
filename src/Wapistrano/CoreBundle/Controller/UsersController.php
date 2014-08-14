<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Users;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\UsersTypeAdd;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/users")
 * @Breadcrumb("Users", routeName="usersList")
 */
class UsersController extends Controller
{

    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('usersAdd');
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

        return array('barTitle' =>  'Users list', 'sectionAction' => $this->getSectionAction(), 'users'=>$users, "flashMessage" => $flashMessage);
    }

    /**
     * @Route("/add", name="usersAdd")
     * @Template("WapistranoCoreBundle:Form:users_update.html.twig")
     */
    public function addAction(Request $request)
    {
        $userService = $this->container->get('wapistrano_core.user');
        $twigVars["barTitle"] = "Add new user ";
        $twigVars["sectionAction"] = $this->getSectionAction();
        $formHandle = $userService->getFormAdd();

        if ("sent" == $userService->getFormStatus()) {
            return $this->redirect($this->generateUrl('usersList'));
        } else {
            $twigVars += $formHandle->getFormTwigVars();
            return $twigVars;
        }
    }

    /**
     * @Route("/{id}/edit", name="usersEdit")
     * @Breadcrumb("{user.login}", routeName="usersEdit", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle:Form:users_update.html.twig")
     */
    public function updateAction(Request $request, Users $user)
    {
        $userService = $this->container->get('wapistrano_core.user');
        $twigVars["barTitle"] = "Edit ".$user->getLogin();
        $twigVars["sectionAction"] = $this->getSectionAction();
        $formHandle = $userService->getFormEdit($user);

        if ("sent" == $userService->getFormStatus()) {
            return $this->redirect($this->generateUrl('usersList'));
        } else {
            $twigVars += $formHandle->getFormTwigVars();
            return $twigVars;
        }
    }

    /**
     * @Route("/{id}/delete", name="usersDelete")
     */
    public function deleteAction(Request $request, Users $user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->remove($user);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'User '.$user->getLogin().' deleted');
        return $this->redirect($this->generateUrl('usersList'));
    }

    /**
     * @Route("/project/", name="usersProjectsList")
     * @Template("WapistranoCoreBundle::users_projects_list.html.twig")
     */
    public function usersProjectsListAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $projects = $em->getRepository('WapistranoCoreBundle:Projects')->findBy(array(), array("name" => "ASC"));

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array( 'barTitle' =>  'Projects list', 'sectionAction' => $this->getSectionAction(), 'projects'=>$projects, "flashMessage" => $flashMessage);
    }

    /**
     * @Route("/project/{id}/add", name="usersProjectAdd")
     * @Breadcrumb("{project.name}", routeName="usersProjectAdd", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle:Form:users_project_update.html.twig")
     */
    public function usersProjectAddAction(Request $request, Projects $project)
    {
        $userService = $this->container->get('wapistrano_core.user');
        $twigVars["barTitle"] = "Manage users ".$project->getName(). " project";
        $formHandle = $userService->getFormProjectEdit($project);

        if ("sent" == $userService->getFormStatus()) {
            return $this->redirect($this->generateUrl('usersProjectsList'));
        } else {
            $twigVars += $formHandle->getFormTwigVars();
            return $twigVars;
        }
    }


}
