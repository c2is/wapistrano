<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\hosts;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Wapistrano\CoreBundle\Form\hostsTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/hosts")
 * @Breadcrumb("Hosts", routeName="hostsList")
 */
class HostsController extends Controller
{
    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('hostsAdd');
        }
        return $this->sectionAction;
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

        return array('barTitle' =>  'Hosts list', 'sectionAction' => $this->getSectionAction(), 'hosts'=>$hosts, "flashMessage" => $flashMessage);
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
        return array('barTitle' =>  'Add new host', 'sectionAction' => $this->getSectionAction(), 'form' => $form->createView());
    }

    /**
     * @Route("/{id}/edit", name="hostsEdit")
     * @Breadcrumb("{host.name}", routeName="hostsEdit", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle:Form:hosts_update.html.twig")
     */
    public function updateAction(Request $request, Hosts $host)
    {
        $hostType = new hostsTypeAdd();

        $form = $this->get('form.factory')->create($hostType, $host);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $host->setCreatedAt($today);
            $host = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($host);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Host '.$host->getName().' updated');

            return $this->redirect($this->generateUrl('hostsList'));
        }

        return array('barTitle' =>  $host->getName(), 'sectionAction' => $this->getSectionAction(), 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/delete", name="hostsDelete")
     */
    public function deleteAction(Request $request, $id)
    {
        $session = $request->getSession();
        $em = $this->container->get('doctrine')->getManager();
        $Host = $em->getRepository('WapistranoCoreBundle:hosts')->findOneBy(array("id" => $id));

        $Roles = $em->getRepository('WapistranoCoreBundle:Roles')->findBy(array("host" => $id));
        if (null != $Roles) {
            $msg = array();
            foreach ($Roles as $role) {
                $msg[] = $role->getStage()->getName(). " (" . $role->getStage()->getProject()->getName().")";
            }
            $session->getFlashBag()->add('notice', 'Host '.$Host->getName()." could not be deleted because it's used in stage(s) : ".implode(", ", $msg));
            return $this->redirect($this->generateUrl('hostsList'));
        }

        $em->remove($Host);
        $em->flush();


        $session->getFlashBag()->add('notice', 'Host '.$Host->getName().' deleted');
        return $this->redirect($this->generateUrl('hostsList'));
    }
}
