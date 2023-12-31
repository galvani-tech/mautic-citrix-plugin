<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Form\Type;

use Mautic\IntegrationsBundle\Form\Type\NotBlankIfPublishedConstraintTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigAuthType extends AbstractType
{
    use NotBlankIfPublishedConstraintTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $clientSecret = $options['integration']->getIntegrationConfiguration()->getApiKeys()['client_secret'] ?? null;

        $builder->add(
            'client_id',
            TextType::class,
            [
                'label'      => 'mautic.integration.keyfield.clientid',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    $this->getNotBlankConstraint(),
                ],
            ]
        );

        $builder->add(
            'client_secret',
            PasswordType::class,
            [
                'label'      => 'mautic.integration.keyfield.clientsecret',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    $this->getNotBlankConstraint(),
                ],
                'empty_data' => $clientSecret,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['integration' => null]);
    }
}
