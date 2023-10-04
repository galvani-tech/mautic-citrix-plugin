<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options); // TODO: Change the autogenerated stub
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options); // TODO: Change the autogenerated stub
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'disabled'    => true,
            'required'    => false,
            'mapped'      => false,
            'integration' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user_data';
    }
}
