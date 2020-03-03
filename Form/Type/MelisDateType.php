<?php

namespace MelisPlatformFrameworkSymfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MelisDateType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'melisdate';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'melisdate';
    }
}