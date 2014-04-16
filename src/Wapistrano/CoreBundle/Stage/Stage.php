<?php

namespace Wapistrano\CoreBundle\Stage;

use Wapistrano\CoreBundle\Entity\Stages;
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
        $configurationType = new StagesTypeAdd();
        $configuration = new Stages();


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

        $formUrl = $this->router->generate("projectsStageAdd", array("id"=>$this->getProjectId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:stage.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "projectId"=>$this->projectId, "popinTitle" => "Add a stage"));

    }

    public function displayFormEdit() {
        $stageType = new StagesTypeAdd();
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')
            ->findOneBy(array("projectId" => $this->getProjectId(), "id" => $this->getStageId()));


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

    public function getStageList() {
        $stages = $this->em->getRepository('WapistranoCoreBundle:Stages')->findBy(array("projectId" => $this->getProjectId()));

        return $stages;
    }

    public function delete($id) {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')->findOneBy(array("id" => $id));
        $this->em->remove($stage);
        $this->em->flush();
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
