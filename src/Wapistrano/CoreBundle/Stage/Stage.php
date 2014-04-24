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

    public $projectId;
    public $stageId;

    public function __construct(RequestStack $requestStack, $em, $form, \Twig_Environment $twig, $router)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->twig = $twig;
        $this->router = $router;
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
    }

    public function manageRecipes($recipes) {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')
            ->findOneBy(array("project" => $this->getProjectId(), "id" => $this->getStageId()));


        $stage->setRecipe($recipes);

        $this->em->persist($stage);
        $this->em->flush();

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
