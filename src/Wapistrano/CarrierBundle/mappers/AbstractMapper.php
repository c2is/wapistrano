<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */

namespace Wapistrano\CarrierBundle\mappers;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractMapper {

    protected $crawler;
    protected $em;
    protected $repository;
    protected $nodeName;
    protected $objectMapped;
    protected $uniqueConstraintProperty;

    abstract protected function getNodeName();
    abstract protected function getUniqueConstraintProperty();

    protected function setCrawler(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    protected function setRepository($repository)
    {
        $this->repository = $this->em->getRepository($repository);
    }

    protected function setManager($manager)
    {
        $this->em = $manager;
    }

    protected function getProperties()
    {
        $properties = $this->crawler->filter($this->getNodeName())->children();

        $flatProperties = array();
        foreach($properties as $domElem) {
            if(count($this->crawler->filter($domElem->nodeName)->children()) == 0) {
                $flatProperties[$domElem->nodeName] = $this->crawler->filter($domElem->nodeName)->text();
            }
        }

        return $flatProperties;

    }

    protected function setObjectMapped($object)
    {

        $properties = $this->getProperties();

        foreach($properties as $name => $value) {
            $methodName = "set".ucfirst($name);
            $object->$methodName($value);
        }
        /*
        $test = $this->repository->findOneBy(array("name" => $project->getName()));
        if(is_object($test)) {
            $project->setName($project->getName()." imported");
        }
        */
        $this->objectMapped = $object;
    }

    protected function getObjectMapped()
    {
        return $this->objectMapped;
    }

    public function object()
    {
        return $this->getObjectMapped();
    }
} 