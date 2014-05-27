<?php

namespace Wapistrano\CoreBundle\Stage;

use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Entity\Recipes;
use Wapistrano\CoreBundle\Form\StagesTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class Stage
{
    private $em;
    private $request;
    private $form;
    private $twig;
    private $router;
    private $gearman;
    private $logger;

    public $projectId;
    public $stageId;

    public function __construct(RequestStack $requestStack, $em, $form, \Twig_Environment $twig, $router, $gearman, $logger)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->twig = $twig;
        $this->router = $router;
        $this->gearman = $gearman;
        $this->logger = $logger;
    }

    public function displayFormAdd() {
        $stageType = new StagesTypeAdd();
        $stage = new Stages();

        $project = $this->em->getRepository('WapistranoCoreBundle:Projects')
            ->findOneBy(array( "id" => $this->getProjectId()));


        $form = $this->form->create($stageType, $stage);

        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $stage->setCreatedAt($today);

            $stage->setProject($project);

            $this->em->persist($stage);
            $this->em->flush();

            $job = $this->publishStage($this->getProjectId(), $stage->getId());
            $job->delRedisLog($job->getJobHandle());

        }

        $formUrl = $this->router->generate("projectsStageAdd", array("id"=>$this->getProjectId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:stage.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "projectId"=>$this->projectId, "popinTitle" => "Add a stage"));

    }

    public function displayFormEdit() {
        $stageType = new StagesTypeAdd();
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')
            ->findOneBy(array("project" => $this->getProjectId(), "id" => $this->getStageId()));

        $form = $this->form->create($stageType, $stage);

        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $stage->setUpdatedAt($today);
            $stage = $form->getData();

            $this->em->persist($stage);
            $this->em->flush();

            $this->publishStage($this->getProjectId(), $this->getStageId());
        }

        $formUrl = $this->router->generate("projectsStageEdit", array("projectId"=>$this->getProjectId(), "stageId"=>$this->getStageId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:stage.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "projectId"=>$this->projectId, "popinTitle" => "Edit a stage"));

    }

    public function displayFormRecipeManage() {

        $form = $this->form->create();

        $form->add('recipes', 'entity', array(
            'class'   => "WapistranoCoreBundle:Recipes",
            'property'   => "name",
            'data' => $this->getRecipes(),
            'multiple'  => true,
            'expanded'  => true,
        ));

        $form->add('save', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $recipes = array();
            foreach ($data["recipes"] as $recipe) {
                $recipes[] = $recipe;
            }

            $this->manageRecipes($recipes);

            $this->publishStage($this->getProjectId(), $this->getStageId());

            return "redirect";
        }
        $options = $form->get('recipes')->getConfig()->getOptions();
        $choices = $options['choice_list']->getChoices();

        $formUrl = $this->router->generate("projectsStageRecipeManage", array("projectId"=>$this->getProjectId(), "stageId"=>$this->getStageId()));

        return $this->twig->render("WapistranoCoreBundle:Form:stage_recipes.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "choices" => $choices));

    }

    public function getStageList() {
        $stages = $this->em->getRepository('WapistranoCoreBundle:Stages')->findBy(array("project" => $this->getProjectId()));

        return $stages;
    }

    public function delete($id) {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')->findOneBy(array("id" => $id));
        $this->em->remove($stage);
        $this->em->flush();

        $gmclient = $this->gearman;

        $jobId = $gmclient->doBackgroundAsync("delete_stage", json_encode(array("projectId"=>$this->getProjectId(), "stageId" => (string) $id)));

    }

    public function manageRecipes($recipes) {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')
            ->findOneBy(array("project" => $this->getProjectId(), "id" => $this->getStageId()));


        $stage->setRecipe($recipes);

        $this->em->persist($stage);
        $this->em->flush();

        $this->publishStage($this->getProjectId(), $this->getStageId());

    }

    public function getRecipes() {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')
            ->findOneBy(array("project" => $this->getProjectId(), "id" => $this->getStageId()));

        return $stage->getRecipe();
    }

    public function getAllRecipes() {
        $recipes = $this->em->getRepository('WapistranoCoreBundle:Recipes')
            ->findAll();

        return $recipes;
    }

    public function getRoles() {
        $roles = $this->em->getRepository('WapistranoCoreBundle:Roles')->findBy(array("stage" => $this->getStageId()));

        return $roles;
    }

    public function getConfigurations() {
        $projectConfigurations = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')->findBy(array("projectId" => $this->getProjectId(), "stageId" => NULL), array("name" => "ASC"));
        $stageConfigurations = $this->em->getRepository('WapistranoCoreBundle:ConfigurationParameters')->findBy(array("stageId" => $this->getStageId()), array("name" => "ASC"));

        $configurations = array();
        foreach($projectConfigurations as $configuration) {
            $configurations[$configuration->getName()] = $configuration;
        }
        foreach($stageConfigurations as $configuration) {
            $configurations[$configuration->getName()] = $configuration;
        }
        ksort($configurations);
        return $configurations;
    }

    /*
     * build the .rb filecontent and sent it to broker
     */
    public function publishStage($projectId, $stageId, array $confOverrided = null) {
        $this->setProjectId($projectId);
        $this->setStageId($stageId);

        $recipes = array();
        foreach($this->getRecipes() as $recipe) {
            $recipes[] = $recipe->getBody();

        }

        $roles = array();
        foreach($this->getRoles() as $role) {
            $sshPort = "22";
            if("" != $role->getSshPort()) {
                $sshPort = $role->getSshPort();
            }
            $roles[] = 'role :'.$role->getName().', "'.$role->getHost()->getName().':'.$sshPort.'"';

        }

        $configurations = array();
        foreach($this->getConfigurations() as $confName=>$configuration) {
            if(null != $confOverrided && array_key_exists($confName, $confOverrided)) {
                $confValue = $confOverrided[$confName]->getValue();
            } else {
                $confValue = $configuration->getValue();
            }

            if(strpos($confValue, ":") !== 0 && "false" != $confValue && "true" != $confValue) {
                $confValue = '"'.$confValue.'"';
            }
            $configurations[] = 'set :'.$confName.', '.$confValue;
        }

        $configurationsBlock = implode("\n", $configurations);
        $rolesBlock = implode("\n", $roles);
        $recipeBlock = implode(" \n", $recipes);

        $gmclient = $this->gearman;
        $this->logger->info("Sending job 'publish_stage' to Gearman, projectId: ".$projectId." stageId: ".$stageId);
        $this->logger->debug($configurationsBlock."\n".$rolesBlock."\n".$recipeBlock);
        $gmclient->doBackgroundSync("publish_stage", json_encode(array("projectId"=> (string) $projectId, "stageId" => (string) $stageId, "content" => $configurationsBlock."\n".$rolesBlock."\n".$recipeBlock  )));

        return $gmclient;

    }

    /*
     * build the .rb filecontent and sent it to broker
     */
    public function deployStage($task) {

        $gmclient = $this->gearman;

        $exec = "cap ".(string) $this->getStageId()." ".$task;

        $this->logger->info("Sending job 'cap_command' to Gearman, projectId: ".$this->getProjectId()." capCommand: ".$exec);
        $gmclient->doBackgroundAsync("cap_command", json_encode(array("projectId"=>(string) $this->getProjectId(), "capCommand" => $exec )));

        return $gmclient;

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
