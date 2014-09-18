<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */

namespace Wapistrano\CarrierBundle\Subscriber;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

class JmsSubscriber implements EventSubscriberInterface {

    private $entityManager;

    public function setEm($em) {
        $this->entityManager = $em;
    }
    public static function getSubscribedEvents()
    {

        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'format' => 'xml',
                'class' => 'Wapistrano\CoreBundle\Entity\Projects', // fully qualified name here
                'method' => 'onPreSerializeProjects',
            ],
            [
                'event' => Events::PRE_SERIALIZE,
                'format' => 'xml',
                'class' => 'Wapistrano\CoreBundle\Entity\Stages', // fully qualified name here
                'method' => 'onPreSerializeStages',
            ],
            [
                'event' => Events::PRE_DESERIALIZE,
                'format' => 'json',
                'class' => 'Task',
                'method' => 'onPreDeserializeTaskJson',
            ]
        ];
    }

    public function onPreSerializeProjects(PreSerializeEvent $event)
    {
        $project = $event->getObject();

        $projectConfigurations = $this->entityManager->getRepository("WapistranoCoreBundle:ConfigurationParameters")->findBy(array("projectId" => $project->getId(), "type" => "ProjectConfiguration"));

        $project->setConfigurationParameters($projectConfigurations);

    }

    public function onPreSerializeStages(PreSerializeEvent $event)
    {
        $stage = $event->getObject();

        $stageConfigurations = $this->entityManager->getRepository("WapistranoCoreBundle:ConfigurationParameters")->findBy(array("stageId" => $stage->getId(), "type" => "StageConfiguration"));

        $stage->setConfigurationParameters($stageConfigurations);

    }

    public function onPreDeserializeTaskJson(PreDeserializeEvent $event)
    {
        $data = $event->getData();

        $data['status'] = Task::getStatusFromLabel($data['status']);

        $event->setData($data);
    }
} 