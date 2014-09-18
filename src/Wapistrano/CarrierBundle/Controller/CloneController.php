<?php

namespace Wapistrano\CarrierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CarrierBundle\Importer;
use Wapistrano\CarrierBundle\Exporter;

class CloneController extends Controller
{
    /**
     * @Route("/projects/{id}/clone", name="projectsClone")
     * @Breadcrumb("Projects", routeName="projectsHome", routeParameters={"id"="{id}"})
     * @Breadcrumb("Clone", routeName="projectsClone", routeParameters={"id"="{id}"})
     */
    public function indexAction(Projects $project)
    {
        $importDir = $this->container->getParameter('wapistrano_carrier.transit_dir');
        $filePath = $importDir."project_".$project->getId().".xml";

        $this->export($project, $filePath);
        $id = $this->import($filePath);
        return $this->redirect($this->generateUrl('projectsHome', array("id" => $id)));
    }

    private function export(Projects $project, $filePath)
    {
        $exporter = new exporter($this->container->get('doctrine')->getManager());
        $exporter->export($project, $serializer = $this->container->get('jms_serializer'), $filePath);
    }

    private function import($filePath)
    {
        $importer = new importer($this->container->get('doctrine')->getManager());
        $serializer = $this->container->get('jms_serializer');
        return $importer->import($filePath, $serializer);
    }
}
