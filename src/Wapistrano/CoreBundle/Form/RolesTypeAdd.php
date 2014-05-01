<?php

namespace Wapistrano\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;

class RolesTypeAdd extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('name', 'choice', array(
                'choices' => array("web" => "web", "app" => "app", "db" => "db"),
                'empty_value' => 'Choose',
            ))
            ->add('host', 'entity', array(
                'label' => 'Host',
                'class'   => "WapistranoCoreBundle:Hosts",
                'property'   => "name",
                'multiple' => false,
                'empty_value' => 'Choose',
                "required" => false
            ))
            ->add('sshPort')
            ->add('primary', 'checkbox', array("required" => false))
            ->add('noRelease', 'checkbox', array("required" => false))
            ->add('noSymlink', 'checkbox', array("required" => false))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $role = $event->getData();
            $form = $event->getForm();

            // vérifie si l'objet Roles est "nouveau"
            // Si aucune donnée n'est passée au formulaire, la donnée est "null".
            // Ce doit être considéré comme un nouveau "Role"
            if (!$role || null === $role->getId()) {
                $form->add('hostName', null, array("mapped" => false, "required" => false))
                    ->add('hostAlias', null, array("mapped" => false, "required" => false))
                    ->add('hostDescription', "textarea", array("mapped" => false, "required" => false))
                ;

            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
            $form = $event->getForm();

            $host = $form->get("host")->getData();
            $newHost = $form->get("hostName")->getData();

            if(null == $host && "" == $newHost){
                $form->addError(new FormError('Yo have to choose a host or ask for creating a new one'));
            }


        });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wapistrano\CoreBundle\Entity\Roles'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'wapistrano_corebundle_roles';
    }
}
