<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Wapistrano\CarrierBundle;

use Symfony\Component\DomCrawler\Crawler;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CarrierBundle\mappers\ProjectMapper;
use Wapistrano\CarrierBundle\mappers\ConfigurationParametersMapper;

class Importer {

    private $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    function import($filePath, $serializer)
    {
        try {
            $xml = file_get_contents($filePath);
        } catch(\Exception $e) {
            throw $e;
        }

        $crawler = new Crawler($xml);
        $filtered = $crawler->filterXPath("//project");

        $xml = simplexml_load_string($filePath);

       $test = sprintf("<project>%s</project>", $filtered->current());

        echo "|".$test."|";
        echo $serializer->deserialize($test, 'Wapistrano\CoreBundle\Entity\Projects', "xml");

        die();

        $projectMapper  = new ProjectMapper($this->em, $filtered);
        $xml =$serializer->serialize($projectMapper->getObjectStored("91"), "xml");
        echo "<pre>".$xml."</pre>";




        echo $projectMapper->object()->getName();
        //$projectMapper->save();
        echo  $projectMapper->object()->getId();

        $filtered = $crawler->filterXPath("//project/stages/stage")->eq(0)->filterXPath("//configuration_parameters/configuration")->eq(0);
        $confMapper = new ConfigurationParametersMapper($this->em, $filtered);
        echo $confMapper->object()->getName();

        $projectMapper  = new ProjectMapper($this->em);


        //$array = $serializer->normalize($projectMapper->getObjectStored("91"));
        //$array = $serializer->normalize(new Projects());

        //echo $serializer->serialize($array, 'xml');


        //echo $filtered->text();
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