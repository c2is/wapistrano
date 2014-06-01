<?php

namespace Wapistrano\CoreBundle\Configuration;

use Wapistrano\CoreBundle\Entity\ConfigurationParameters;
use Wapistrano\CoreBundle\Form\ConfigurationParametersTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Configuration
{
    private $em;
    private $request;
    private $form;
    private $twig;
    private $router;
    private $stage;

    public $projectId;
    public $configurationId;
    public $stageId;

    public function __construct(RequestStack $requestStack, $em, $form, \Twig_Environment $twig, $router, $stage)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->twig = $twig;
        $this->router = $router;
        $this->stage = $stage;
    }

    public function displayFormAdd() {
        $configurationType = new ConfigurationParametersTypeAdd();
        $configuration = new ConfigurationParameters();
        $twigVars = array();

        $form = $this->form->create($configurationType, $configuration);
        $form->add('project_id', 'hidden', array(
            'data' => $this->projectId
        ));

        if ("" != $this->getStageId()) {
            $form->add('stage_id', 'hidden', array(
                'data' => $this->getStageId()
            ));
        }

        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $configuration->setCreatedAt($today);
            $configuration = $form->getData();

            $this->em->persist($configuration);
            $this->em->flush();

            $this->stage->setProjectId($this->getProjectId());
            // if it's a generic project configuration, update all stages
            if (null == $configuration->getStageId()) {
                foreach ($this->stage->getStageList() as $stage) {
                    $job = $this->stage->publishStage($this->getProjectId(), $stage->getId());
                    if(! is_object($job)) {
                        $twigVars["flashMessagePopinText"] .= "<br>".$job;
                    }

                }
            } else {
                $job = $this->stage->publishStage($this->getProjectId(), $configuration->getStageId());
                if(! is_object($job)) {
                    $twigVars["flashMessagePopinText"] = $job;
                }
            }

        }

        if ("" != $this->getStageId()) {
            $formUrl = $this->router->generate("stageConfigurationAdd", array("projectId"=>$this->getProjectId(), "stageId" => $this->getStageId()));
        } else {
            $formUrl = $this->router->generate("projectsConfigurationAdd", array("id"=>$this->getProjectId()));
        }

        $twigVars["form"] = $form->createView();
        $twigVars["formUrl"] = $formUrl;
        $twigVars["projectId"] = $this->projectId;
        $twigVars["popinTitle"] = "Add a configuration";

        return $this->twig->render("WapistranoCoreBundle:Popin:configuration.html.twig",
            $twigVars);

    }

    public function displayFormEdit() {
        $configurationType = new ConfigurationParametersTypeAdd();

        $configuration = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')
            ->findOneBy(array("projectId" => $this->getProjectId(), "id" => $this->getConfigurationId()));



        $form = $this->form->create($configurationType, $configuration);

        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $configuration->setUpdatedAt($today);
            $configuration = $form->getData();

            $this->em->persist($configuration);
            $this->em->flush();

            $this->stage->setProjectId($this->getProjectId());
            // if it's a generic project configuration, update all stages
            if (null == $configuration->getStageId()) {
                foreach ($this->stage->getStageList() as $stage) {
                    $job = $this->stage->publishStage($this->getProjectId(), $configuration->getStageId());
                    if(! is_object($job)) {
                        $twigVars["flashMessagePopinText"] = $job;
                    }
                }
            } else {
                $job = $this->stage->publishStage($this->getProjectId(), $configuration->getStageId());
                if(! is_object($job)) {
                    $twigVars["flashMessagePopinText"] = $job;
                }
            }

        }

        $formUrl = $this->router->generate("projectsConfigurationEdit", array("projectId"=>$this->getProjectId(), "configurationId"=>$this->getConfigurationId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:configuration.html.twig",
            array("form"=>$form->createView(), "projectId"=>$this->projectId, "popinTitle" => "Edit a configuration", "formUrl" => $formUrl));

    }

    public function getProjectConfigurationList() {
        $configurations = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')->findBy(array("projectId" => $this->getProjectId(), "stageId" => NULL), array("name" => "ASC"));

        return $configurations;
    }

    public function getStageConfigurationList() {
        $configurations = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')->findBy(array("stageId" => $this->getStageId()), array("name" => "ASC"));

        return $configurations;
    }

    public function delete($id) {
        $configuration = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')->findOneBy(array("id" => $id));
        $this->em->remove($configuration);
        $this->em->flush();

        $this->stage->setProjectId($this->getProjectId());
        // if it's a generic project configuration, update all stages
        if (null == $configuration->getStageId()) {
            foreach ($this->stage->getStageList() as $stage) {
                $this->stage->publishStage($this->getProjectId(), $stage->getId());
            }
        } else {
            $this->stage->publishStage($this->getProjectId(), $configuration->getStageId());
        }


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

    /**
     * @param mixed $configurationId
     */
    public function setConfigurationId($configurationId)
    {
        $this->configurationId = $configurationId;
    }

    /**
     * @return mixed
     */
    public function getConfigurationId()
    {
        return $this->configurationId;
    }


}
