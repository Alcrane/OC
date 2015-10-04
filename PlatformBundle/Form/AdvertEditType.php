<?php

//-- src\OC\PlatformBundle\Form\AdvertEditType.php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvertEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('date')
            ;
    }
    
   /**
     * @return string
     */
    public function getName()
    {
        return 'oc_platformbundle_advert_edit';
    }
    
    /**
     * @return string
     */
    public function getParent()
    {
        return new AdvertType();
    }
}
