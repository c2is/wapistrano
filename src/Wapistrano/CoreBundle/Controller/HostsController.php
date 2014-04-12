<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\hosts;
use Wapistrano\CoreBundle\Form\hostsTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/hosts")
 */
class HostsController extends Controller
{
    private $sectionTitle;
    private $sectionAction;
    private $sectionUrl;

    public function getSectionTitle() {
        if (null == $this->sectionTitle) {
            $this->sectionTitle = 'hosts';
        }
        return $this->sectionTitle;
    }

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('hostsAdd');
        }
        return $this->sectionAction;
    }

    public function getSectionUrl() {
        if (null == $this->sectionUrl) {
            $this->sectionUrl = $this->generateUrl('hostsList');
        }
        return $this->sectionUrl;
    }
    /**
     * @Route("/", name="hostsList")
     * @Template("WapistranoCoreBundle::hosts_list.html.twig")
     */
    public function listAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $hosts = $em->getRepository('WapistranoCoreBundle:hosts')->findAll();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'List', 'hosts'=>$hosts, "flashMessage" => $flashMessage);
    }

    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, name="hostsHome")
     * @Template("WapistranoCoreBundle::hosts_list.html.twig")
     */
    public function indexAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $hosts = $em->getRepository('WapistranoCoreBundle:hosts')->findAll();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'List', 'hosts'=>$hosts, "flashMessage" => $flashMessage);
    }

    public function getUrlAction($action, $id = ""){

        if ("" == $id) {
            return new Response($this->generateUrl('hosts'.$action));
        } else {
            return new Response($this->generateUrl('hosts'.$action, array("id" => $id)));

        }

    }
    /**
     * @Route("/add", name="hostsAdd")
     * @Template("WapistranoCoreBundle:Form:hosts_create.html.twig")
     */
    public function addAction(Request $request)
    {
        $HostType = new hostsTypeAdd();
        $Host = new hosts();


        $form = $this->get('form.factory')->create($HostType, $Host);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $Host->setCreatedAt($today);
            $Host = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($Host);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Host '.$Host->getName().' added');

            return $this->redirect($this->generateUrl('hostsList'));
        }
        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Add', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/edit", name="hostsEdit")
     * @Template("WapistranoCoreBundle:Form:hosts_update.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        $em = $this->container->get('doctrine')->getManager();
        $Host = $em->getRepository('WapistranoCoreBundle:hosts')->findOneBy(array("id" => $id));

        $HostType = new hostsTypeAdd();


        $form = $this->get('form.factory')->create($HostType, $Host);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $Host->setCreatedAt($today);
            $Host = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($Host);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Host '.$Host->getName().' updated');

            return $this->redirect($this->generateUrl('hostsList'));
        }
        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Add', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/delete", name="hostsDelete")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $Host = $em->getRepository('WapistranoCoreBundle:hosts')->findOneBy(array("id" => $id));
        $em->remove($Host);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Host '.$Host->getName().'deleted');
        return $this->redirect($this->generateUrl('hostsHome'));
    }
}
