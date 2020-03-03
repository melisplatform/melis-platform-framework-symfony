<?php

namespace MelisPlatformFrameworkSymfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MelisTinyMceType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getParent()
    {
        return TextareaType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'melistinymce';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'melistinymce';
    }
}