<?php

namespace Wapistrano\CarrierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CarrierBundle\Importer;
use Wapistrano\CarrierBundle\Exporter;
use Wapistrano\CarrierBundle\Form\ImportTypeAdd;

class CarrierController extends Controller
{
    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('projectsImport');
        }

        return $this->sectionAction;
    }
    /**
     * @Route("/projects/{id}/clone", name="projectsClone")
     * @Breadcrumb("Projects", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Breadcrumb("Clone", routeName="projectsClone", routeParameters={"id"="{id}"})
     */
    public function indexAction(Request $request, Projects $project)
    {
        $importDir = $this->container->getParameter('wapistrano_carrier.transit_dir');
        $securityContext = $this->container->get('security.context');


        $filePath = $importDir."project_".$project->getId().".xml";
        $this->export($project, $filePath);
        $id = $this->import($filePath, $securityContext);
        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Project '.$project->getName().' cloned');
        return $this->redirect($this->generateUrl('projectsHome', array("id" => $id)));
    }

    /**
     * @Route("/projects/{id}/export", name="projectsExport")
     * @Breadcrumb("Projects", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Breadcrumb("Export", routeName="projectsExport", routeParameters={"id"="{id}"})
     */
    public function exportAction(Request $request, Projects $project)
    {
        $importDir = $this->container->getParameter('wapistrano_carrier.transit_dir');

        $fileName = "wapistrano-project-".$project->getName().".xml";
        $filePath = $importDir."project_".$project->getId().".xml";

        $this->export($project, $filePath);

        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName,
            iconv('UTF-8', 'ASCII//TRANSLIT', $fileName)
        );

        return $response;
    }

    /**
     * @Route("/projects/import", name="projectsImport")
     * @Breadcrumb("Projects", routeName="projectsList")
     * @Breadcrumb("Import", routeName="projectsImport")
     * @Template("WapistranoCarrierBundle:Form:import.html.twig")
     */
    public function importAction(Request $request)
    {
        $importDir = $this->container->getParameter('wapistrano_carrier.transit_dir');
        $securityContext = $this->container->get('security.context');

        $importType = new ImportTypeAdd();


        $form = $this->get('form.factory')->create($importType);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fileName = md5(microtime())."-tmp.xml";
            $form['attachment']->getData()->move($importDir, $fileName);
            // $id = $this->import($filePath, $securityContext);
            // $session = $request->getSession();
           // $session->getFlashBag()->add('notice', 'Project '.$project->getName().' cloned');

            // return "";// $this->redirect($this->generateUrl('projectsHome', array("id" => $id)));
        }

        return array('barTitle' =>  'Import project', 'form' => $form->createView(), "flashMessage" => "");
    }

    private function export(Projects $project, $filePath)
    {
        $exporter = new exporter($this->container->get('doctrine')->getManager());
        $exporter->export($project, $serializer = $this->container->get('jms_serializer'), $filePath);
    }

    private function import($filePath, $securityContext)
    {
        $importer = new importer($this->container->get('doctrine')->getManager());
        $serializer = $this->container->get('jms_serializer');
        return $importer->import($filePath, $serializer, $securityContext);
    }
}
