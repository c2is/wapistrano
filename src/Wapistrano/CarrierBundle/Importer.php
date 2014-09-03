<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Wapistrano\CarrierBundle;

use Symfony\Component\DomCrawler\Crawler;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CarrierBundle\mappers\ProjectMapper;

class Importer {

    private $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    function import($filePath)
    {
        try {
            $xml = file_get_contents($filePath);
        } catch(\Exception $e) {
            throw $e;
        }

        $crawler = new Crawler($xml);

        $projectMapper  = new ProjectMapper($crawler, $this->em);
        echo $projectMapper->object()->getDescription();
        /*
        echo $project->getName();


        $crawler->filter('recipe')->each(function ($node) {
            $node->children()->each(function ($nodeChild) {

                foreach ($nodeChild as $domElement) {
                    print "<br>".$domElement->nodeName."--".$nodeChild->text();
                }

            });
        });
        */
    }

} 