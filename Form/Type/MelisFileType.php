<?php

namespace MelisPlatformFrameworkSymfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MelisFileType extends AbstractType
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
        return 'melisfile';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'melisfile';
    }
}