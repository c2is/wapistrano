<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */

namespace Wapistrano\CarrierBundle\mappers;


class DbPercolator {
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function save($entity, array $constraints = null) {
        if (null != $constraints) {
            $constraintsBak = $constraints;
            $firstConstraint = array_shift($constraintsBak);

            $searchCriteria = array();
            foreach ($constraints as $constraint) {
                $methodName = "get".ucfirst($constraint);
                $searchCriteria[$constraint] = $entity->$methodName();
            }

            if (null != $test = $this->em->getRepository(get_class($entity))->findOneBy($searchCriteria)) {
                $setMethodName = "set".ucfirst($firstConstraint);
                $getMethodName = "get".ucfirst($firstConstraint);
                $entity->$setMethodName($entity->$getMethodName()." imported");
            }
        }
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function get($entity, $criteria) {

            $searchCriteria = array();
            foreach ($criteria as $criterion) {
                $methodName = "get".ucfirst($criterion);
                $searchCriteria[$criterion] = $entity->$methodName();
            }

        $res = $this->em->getRepository(get_class($entity))->findOneBy($searchCriteria);
        return $res;
    }
} 