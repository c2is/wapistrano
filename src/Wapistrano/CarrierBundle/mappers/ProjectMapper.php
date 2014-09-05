<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */

namespace Wapistrano\CarrierBundle\mappers;
use Symfony\Component\DomCrawler\Crawler;

use Wapistrano\CoreBundle\Entity\Projects;

class ProjectMapper extends AbstractMapper{

    public function __construct($em, Crawler $crawler = null)
    {
        $this->setManager($em);
        $this->setRepository('WapistranoCoreBundle:Projects');

        if(null != $crawler) {
            $this->setCrawler($crawler);
            $object = new Projects();
            $this->setObjectMapped($object);
        }


    }

    protected function getUniqueConstraintProperty()
    {
        return "name";
    }





} 