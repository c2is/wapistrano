<?php

namespace Wapistrano\CoreBundle\Role;

use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Entity\Roles;
use Wapistrano\CoreBundle\Entity\Hosts;
use Wapistrano\CoreBundle\Form\RolesTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class Role
{
    private $em;
    private $request;
    private $form;
    private $twig;
    private $router;

    public $projectId;
    public $stageId;
    public $roleId;
    public $hostId;

    public function __construct(RequestStack $requestStack, $em, $form, \Twig_Environment $twig, $router)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function displayFormAdd() {
        $roleType = new RolesTypeAdd();
        $role = new Roles();

        $stage = $this->em->getRepository('WapistranoCoreBundle:Stages')
            ->findOneBy(array("projectId" => $this->getProjectId(), "id" => $this->getStageId()));

        $form = $this->form->create($roleType, $role);

        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();

            if ("" != $form->get("hostName")->getData()) {
                $host = new Hosts();
                $host->setName($form->get("hostName")->getData());
                $host->setAlias($form->get("hostAlias")->getData());
                $host->setDescription($form->get("hostDescription")->getData());
                $host->setCreatedAt($today);

                $this->em->persist($host);

                $role->setHost($host);
            }

            $today = new \DateTime();
            $role->setCreatedAt($today);
            $role->setStage($stage);

            $this->em->persist($role);
            $this->em->flush();

        }

        $formUrl = $this->router->generate("projectsStageRoleAdd", array("projectId"=>$this->getProjectId(), "stageId"=>$this->getStageId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:role.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "projectId"=>$this->projectId, "popinTitle" => "Add a host"));

    }

    public function displayFormEdit() {
        $roleType = new RolesTypeAdd();
        $role = $this->em->getRepository('WapistranoCoreBundle:Roles')
            ->findOneBy(array("stage" => $this->getStageId(), "id" => $this->getRoleId()));


        $form = $this->form->create($roleType, $role);

        $form->add('saveBottom', 'submit');

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $role->setUpdatedAt($today);

            $this->em->persist($role);
            $this->em->flush();

        }

        $formUrl = $this->router->generate("projectsStageRoleEdit", array("projectId"=>$this->getProjectId(), "stageId"=>$this->getStageId(), "roleId"=>$this->getRoleId()));

        return $this->twig->render("WapistranoCoreBundle:Popin:role.html.twig",
            array("form"=>$form->createView(), "formUrl" => $formUrl, "projectId"=>$this->projectId, "popinTitle" => "Edit a host"));

    }

    public function getRoleList() {
        $roles = $this->em->getRepository('WapistranoCoreBundle:Roles')->findBy(array("stage" => $this->getStageId()));

        return $roles;
    }

    public function delete($id) {
        $stage = $this->em->getRepository('WapistranoCoreBundle:Roles')->findOneBy(array("id" => $id));
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

    /**
     * @param mixed $hostId
     */
    public function setHostId($hostId)
    {
        $this->hostId = $hostId;
    }

    /**
     * @return mixed
     */
    public function getHostId()
    {
        return $this->hostId;
    }

    /**
     * @param mixed $roleId
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

    /**
     * @return mixed
     */
    public function getRoleId()
    {
        return $this->roleId;
    }




}
