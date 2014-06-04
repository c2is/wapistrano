<?php

namespace Wapistrano\ProfileBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @Route("/", name="login")
 */
class SecurityController extends Controller
{
    /**
     *
     * @Route("/login")
     */
    public function loginAction()
    {
        $request = $this->container->get('request');
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('WapistranoProfileBundle::login.html.twig', array(
            'last_username' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
        ));
    }

    /**
     * @return null
     *
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        return null;
    }

    /**
     * @return null
     *
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return null;
    }
}