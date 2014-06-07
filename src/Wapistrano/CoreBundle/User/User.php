<?php

namespace Wapistrano\CoreBundle\User;

use Wapistrano\CoreBundle\Form\UsersTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Wapistrano\CoreBundle\Entity\Users;

class User
{
    private $em;
    private $request;
    private $form;
    private $twig;
    private $router;
    private $securityContext;
    private $formTwigVars;
    private $formStatus;

    public $hostId;

    public function __construct(RequestStack $requestStack, $em, $form, \Twig_Environment $twig, $router, $securityContext)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->form = $form;
        $this->twig = $twig;
        $this->router = $router;
        $this->securityContext = $securityContext;
    }

    public function getFormEdit(Users $user) {
        $this->setFormStatus("building");
        $twigVars = array();
        $userType = new UsersTypeAdd();

        $form = $this->form->create($userType, $user);
        $form->add('saveTop', 'submit');

        if( $this->securityContext->isGranted("ROLE_ADMIN") ){
            $form->add('project', 'entity', array(
                'class'   => "WapistranoCoreBundle:Projects",
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name', 'ASC');
                    },
                'property'   => "name",
                'multiple'  => true,
                'expanded'  => true,
            ));

            $options = $form->get('project')->getConfig()->getOptions();
            $choices = $options['choice_list']->getChoices();
            $twigVars["choices"] = $choices;

            if("" != $user->getDisabled()) {
                $form->add('deactivated', 'checkbox', array("mapped" => false, "data" => true, 'required' => false));
            } else {
                $form->add('deactivated', 'checkbox', array("mapped" => false, "data" => false, 'required' => false));
            }


        }

        $form->add('saveBottom', 'submit', array("label"=>"Save", "attr" => array("class" => "btn btn-default btn-sm")));
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $today = new \DateTime();
            $user->setUpdatedAt($today);
            $user = $form->getData();

            if (true === $form->get("deactivated")->getData()) {
                $user->setDisabled($today);
            } else {
                $user->setDisabled(null);
            }

            $manager = $this->em;
            $manager->persist($user);
            $manager->flush();

            $session = $this->request->getSession();
            $session->getFlashBag()->add('notice', 'User '.$user->getLogin().' updated');
            $this->setFormStatus("sent");
        }

        $twigVars["form"] = $form->createView();
        $this->setFormTwigVars($twigVars);

        return $this;

    }

    public function getFormAdd() {
        $this->setFormStatus("building");
        $twigVars = array();
        $user = new Users();
        $userType = new UsersTypeAdd();

        //print_r($user->getLogin()); die();

        $form = $this->form->create($userType, $user);
        $form->add('saveTop', 'submit');

        if("" != $user->getDisabled()) {
            $form->add('deactivated', 'checkbox', array("mapped" => false, "data" => true, 'required' => false));
        } else {
            $form->add('deactivated', 'checkbox', array("mapped" => false, "data" => false, 'required' => false));
        }

        if( $this->securityContext->isGranted("ROLE_ADMIN") ){
            $form->add('project', 'entity', array(
                'class'   => "WapistranoCoreBundle:Projects",
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name', 'ASC');
                    },
                'property'   => "name",
                'multiple'  => true,
                'expanded'  => true,
            ));

            $options = $form->get('project')->getConfig()->getOptions();
            $choices = $options['choice_list']->getChoices();
            $twigVars["choices"] = $choices;

        }
        //$form->get('email')->setData('@');
        $form->add('saveBottom', 'submit', array("label"=>"Save", "attr" => array("class" => "btn btn-default btn-sm")));
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $today = new \DateTime();
            $user->setCreatedAt($today);
            $user = $form->getData();

            $manager = $this->em;
            $manager->persist($user);
            $manager->flush();

            $session = $this->request->getSession();
            $session->getFlashBag()->add('notice', 'User '.$user->getLogin().' created');
            $this->setFormStatus("sent");
        }

        $twigVars["form"] = $form->createView();
        $this->setFormTwigVars($twigVars);

        return $this;
    }

    /**
     * @param mixed $formTwigVars
     */
    public function setFormTwigVars($formTwigVars)
    {
        $this->formTwigVars = $formTwigVars;
    }

    /**
     * @return mixed
     */
    public function getFormTwigVars()
    {
        return $this->formTwigVars;
    }

    /**
     * @param mixed $formStatus
     */
    public function setFormStatus($formStatus)
    {
        $this->formStatus = $formStatus;
    }

    /**
     * @return mixed
     */
    public function getFormStatus()
    {
        return $this->formStatus;
    }
}
