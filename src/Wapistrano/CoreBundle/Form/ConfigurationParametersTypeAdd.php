<?php

namespace Wapistrano\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigurationParametersTypeAdd extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name')
            ->add('value')
            ->add('prompt_on_deploy', "checkbox", array("required" => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wapistrano\CoreBundle\Entity\ConfigurationParameters'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'wapistrano_corebundle_projects';
    }
}
