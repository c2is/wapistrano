<?php

namespace Wapistrano\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class UsersTypeAdd extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('login')
            ->add('email', null, array('error_bubbling' => true))
            ->add('cryptedPassword', 'repeated', array('type' => 'password', 'required' => false, 'options' => array('required' => false),
                'first_options'  => array('label' => 'Password', 'attr' => array('class' => 'form-control')),
                'second_options' => array('label' => 'Password (confirmation)', 'attr' => array('class' => 'form-control')))
            );
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $user = $event->getData();
            $form = $event->getForm();

            // check if User object is "new" ou if field admin is set to 0
            if (!$user || null === $user->getId() || 0 == $user->getAdmin()) {
                $form->add('admin', 'checkbox', array("data" => false, 'required' => false));


            } else {
                $form->add('admin', 'checkbox', array("data" => true, 'required' => false));
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
            $user = $event->getData();

            if (!$user || null === $user->getId() || false == $user->getAdmin()) {
                $user->setAdmin(0);
            } else {
                $user->setAdmin(1);
            }

        });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wapistrano\CoreBundle\Entity\Users'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'wapistrano_corebundle_users';
    }
}
