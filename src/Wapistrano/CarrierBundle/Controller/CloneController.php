<?php

namespace Wapistrano\CarrierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CarrierBundle\Importer;

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
        $importer = new importer($this->container->get('doctrine')->getManager());
        $serializer = $this->container->get('jms_serializer');
        $importer->import($importDir."template.xml", $serializer);

        return $this->render('WapistranoCarrierBundle:Clone:index.html.twig', array('name' => $project->getName()));
    }
}
