<?php

namespace Wapistrano\CoreBundle\Stage;

use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Entity\Recipes;
use Wapistrano\CoreBundle\Form\StagesTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityRepository;

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
        $twigVars = array();

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
            if(! is_object($job)) {
                $twigVars["flashMessagePopinText"] = $job;
            }

        }

        $formUrl = $this->router->generate("projectsStageAdd", array("id"=>$this->getProjectId()));

        $twigVars["form"] = $form->createView();
        $twigVars["formUrl"] = $formUrl;
        $twigVars["projectId"] = $this->projectId;
        $twigVars["popinTitle"] = "Add a stage";

        return $this->twig->render("WapistranoCoreBundle:Popin:stage.html.twig", $twigVars);

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

        }

        $formUrl = $this->router->generate("projectsStageEdit", array("projectId"=>$this->getProjectId(), "stageId"=>$this->getStageId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:stage.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "projectId"=>$this->projectId, "popinTitle" => "Edit a stage"));

    }

    public function displayFormRecipeManage() {

        $form = $this->form->create();

        $form->add('recipes', 'entity', array(
            'class'   => "WapistranoCoreBundle:Recipes",
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->orderBy('r.name', 'ASC');
                },
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

            $job = $this->publishStage($this->getProjectId(), $this->getStageId());

            if(! is_object($job)) {
                $session = $this->request->getSession();
                $msg = "<br>The configuration has been saved locally";
                $msg .= "<br>It will be at the next stage configuration update, if gearman and workers are up...";
                $session->getFlashBag()->add('notice', $job.$msg);
            }

            return "redirect";
        }
        $options = $form->get('recipes')->getConfig()->getOptions();
        $choices = $options['choice_list']->getChoices();

        $formUrl = $this->router->generate("projectsStageRecipeManage", array("projectId"=>$this->getProjectId(), "stageId"=>$this->getStageId()));

        return $this->twig->render("WapistranoCoreBundle:Form:stage_recipes.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "choices" => $choices));

    }

    public function getStageList() {
        $stages = $this->em->getRepository('WapistranoCoreBundle:Stages')->findBy(array("project" => $this->getProjectId()), array("name" => "ASC"));

        return $stages;
    }

    public function getDeploymentList() {

        $deploymentRepo = $this->em->getRepository('WapistranoCoreBundle:Deployments');
        if("" == $this->getStageId()) {
            $projectStages = $this->em->getRepository('WapistranoCoreBundle:Stages')->findBy(array("project" => $this->getProjectId()), array("name" => "ASC"));
            $stageIds = array();
            foreach ($projectStages as $projectStage) {
                $stageIds[] = $projectStage->getId();
            }
            $deployments = $deploymentRepo->findBy(array("stage" => $stageIds), array("completedAt" => "DESC"));

        } else {
            $deployments = $deploymentRepo->findBy(array("stage" => $this->getStageId()), array("completedAt" => "DESC"));
        }


        return $deployments;
    }

    public function delete($id) {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')->findOneBy(array("id" => $id));

        $gmclient = $this->gearman;
        if(count($gmclient->getBrokerErrors()) == 0) {
            $this->logger->info("Sending job 'delete_stage' to Gearman, stageId: ".$id);
            $gmclient->doBackgroundSync("delete_stage", json_encode(array("projectId"=>$this->getProjectId(), "stageId" => (string) $id)));

            if($gmclient->getTerminateStatus() == "running") {
                $gmclient->delRedisLog($gmclient->getJobHandle());
                return "Timeout thrown while waiting for end of job. Please check if workers are running<br>The stage hasn't been deleted";
            } else {
                $gmclient->delRedisLog($gmclient->getJobHandle());
                $this->em->remove($stage);
                $this->em->flush();
                return $gmclient;
            }

        } else {
            return implode(" ", $gmclient->getBrokerErrors()). "<br>The stage hasn't been deleted";
        }
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

        $recipes = $stage->getRecipe();

        return $recipes;
    }

    public function getAllRecipes() {
        $recipes = $this->em->getRepository('WapistranoCoreBundle:Recipes')
            ->findBy(array(), array("name" => "ASC"));

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
        $recipeBlock .= "\nnamespace :deploy do
           task :restart, :roles => :app, :except => { :no_release => true } do
             # do nothing
           end

           task :start, :roles => :app, :except => { :no_release => true } do
             # do nothing
           end

           task :stop, :roles => :app, :except => { :no_release => true } do
             # do nothing
           end
         end";

        $gmclient = $this->gearman;
        if(count($gmclient->getBrokerErrors()) == 0) {
            $this->logger->info("Sending job 'publish_stage' to Gearman, projectId: ".$projectId." stageId: ".$stageId);
            $this->logger->debug($configurationsBlock."\n".$rolesBlock."\n".$recipeBlock);
            $gmclient->doBackgroundSync("publish_stage", json_encode(array("projectId"=> (string) $projectId, "stageId" => (string) $stageId, "content" => $configurationsBlock."\n".$rolesBlock."\n".$recipeBlock  )));

            if($gmclient->getTerminateStatus() == "running") {
                $gmclient->delRedisLog($gmclient->getJobHandle());
                return "Timeout thrown while waiting for end of job. Please check if workers are running";
            } elseif($gmclient->getTerminateStatus() == "error with Gearman reception") {
                $gmclient->delRedisLog($gmclient->getJobHandle());
                return "Error with Gearman. Please check if brokers are up";
            } elseif($gmclient->getTerminateStatus() == "error") {
                $gmclient->delRedisLog($gmclient->getJobHandle());
                return "Error : a worker failed, check logs";
            } else {
                $gmclient->delRedisLog($gmclient->getJobHandle());
                return $gmclient;
            }

        } else {
            return implode(" ", $gmclient->getBrokerErrors()). "<br>The configuration hasn't been published on deployment server";
        }

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
