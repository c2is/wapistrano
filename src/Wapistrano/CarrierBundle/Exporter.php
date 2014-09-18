<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Wapistrano\CarrierBundle;

use Symfony\Component\DomCrawler\Crawler;
use Wapistrano\CoreBundle\Entity\Projects;

use Wapistrano\CarrierBundle\mappers\XmlSmartCrawler;
use Wapistrano\CarrierBundle\mappers\DbPercolator;


class Exporter {

    private $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    function export(Projects $project, $serializer)
    {
        $xProject = $serializer->serialize($project, "xml");
        echo $xProject;
    }

}