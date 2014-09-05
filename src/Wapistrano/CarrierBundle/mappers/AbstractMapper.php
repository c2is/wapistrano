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

    /*
     * return false or a field name
     */
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
        $properties = $this->crawler->children();

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
            $methodName = "set".$this->camelCase($name);
            $object->$methodName($value);
        }

        if($this->getUniqueConstraintProperty()) {
            $uniquePropertySetMethod = sprintf("set%s", $this->camelCase($this->getUniqueConstraintProperty()));
            $uniquePropertyGetMethod = sprintf("get%s", $this->camelCase($this->getUniqueConstraintProperty()));
            $test = $this->repository->findOneBy(array($this->getUniqueConstraintProperty() => $object->$uniquePropertyGetMethod()));
            if(is_object($test)) {
                $object->$uniquePropertySetMethod($object->$uniquePropertyGetMethod()." imported");
            }
        }

        $this->objectMapped = $object;
    }

    /*
     * Return object created from xml data
     */
    protected function getObjectMapped()
    {
        return $this->objectMapped;
    }

    /*
     * Return existing object in database
     */
    public function getObjectStored($id)
    {
        $object = $this->repository->findOneBy(array("id" => $id));

        return $object;
    }

    protected function camelCase($string)
    {
        $string = str_replace("_", " ", $string);
        $string = ucwords($string);
        $string = str_replace(" ", "", $string);

        return $string;
    }

    public function object()
    {
        return $this->getObjectMapped();
    }

    public function save()
    {
        $this->em->persist($this->objectMapped);
        $this->em->flush();
    }
} 