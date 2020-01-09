<?php

namespace MelisPlatformFrameworkSymfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MelisSwitchType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'melis_switch';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'melis_switch';
    }
}