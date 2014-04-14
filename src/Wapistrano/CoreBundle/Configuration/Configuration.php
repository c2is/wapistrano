<?php

namespace Wapistrano\CoreBundle\Configuration;

use Wapistrano\CoreBundle\Entity\ConfigurationParameters;
use Wapistrano\CoreBundle\Form\ConfigurationParametersTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class Configuration
{
    private $em;
    private $request;
    private $form;
    private $twig;
    public $projectId;
    public $stageId;

    public function __construct(RequestStack $requestStack, $em, $form, \Twig_Environment $twig)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->twig = $twig;
    }

    public function displayFormAdd() {
        $configurationType = new ConfigurationParametersTypeAdd();
        $configuration = new ConfigurationParameters();


        $form = $this->form->create($configurationType, $configuration);
        $form->add('project_id', 'hidden', array(
            'data' => $this->projectId
        ));
        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $configuration->setCreatedAt($today);
            $configuration = $form->getData();

            $this->em->persist($configuration);
            $this->em->flush();

        }

        return $this->twig->render("WapistranoCoreBundle:Popin:configuration.html.twig",
            array("form"=>$form->createView(), "projectId"=>$this->projectId, "popinTitle" => "Add a configuration"));

    }

    public function getConfigurationList() {
        $configurations = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')->findBy(array("projectId" => $this->getProjectId()));

        return $configurations;
    }
    /**
     * @param mixed $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param mixed $stageId
     */
    public function setStageId($stageId)
    {
        $this->stageId = $stageId;
    }

    /**
     * @return mixed
     */
    public function getStageId()
    {
        return $this->stageId;
    }



}
