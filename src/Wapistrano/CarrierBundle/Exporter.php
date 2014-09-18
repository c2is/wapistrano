<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Wapistrano\CarrierBundle;

use Wapistrano\CoreBundle\Entity\Projects;

class Exporter {

    private $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    function export(Projects $project, $serializer, $filePath)
    {
        $xProject = $serializer->serialize($project, "xml");

        file_put_contents($filePath, $xProject);

    }

}