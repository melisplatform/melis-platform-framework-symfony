<?php

namespace App\Bundle\SymfonyTplBundle\Form\Type;

use App\Bundle\SymfonyTplBundle\Entity\SampleEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class SampleEntityFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        dynamic-form-builder;
    }

    /**
     * Set form default value
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class' => SampleEntity::class
        ]);
    }

    /**
     * Remove the form type name
     * so that we can use some of the
     * melis javascript helper and tool
     *
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return null;
    }
}