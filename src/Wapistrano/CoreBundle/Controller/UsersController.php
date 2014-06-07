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

        if("sent" == $userService->getFormStatus()) {
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

        if("sent" == $userService->getFormStatus()) {
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


}
