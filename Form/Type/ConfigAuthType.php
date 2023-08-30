<?php declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Form\Type;

use Mautic\IntegrationsBundle\Form\Type\Auth\Oauth1aTwoLeggedKeysTrait;
use Mautic\IntegrationsBundle\Form\Type\NotBlankIfPublishedConstraintTrait;
use Mautic\PluginBundle\Entity\Integration;
use MauticPlugin\MauticCitrixBundle\Integration\CitrixAbstractIntegration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigAuthType extends AbstractType
{
    //use Oauth1aTwoLeggedKeysTrait;
    use NotBlankIfPublishedConstraintTrait;

    public function __construct() {
        $args = func_get_args();
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('integration', null);

        $resolver->setAllowedTypes('integration', CitrixAbstractIntegration::class);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'app_name',
            TextType::class,
            [
                'label'      => 'mautic.citrix.form.appname',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                ],
                'required'    => true,
                'constraints' => [$this->getNotBlankConstraint()],
            ]
        );

        $builder->add(
            'client_id',
            TextType::class,
            [
                'label'      => 'mautic.citrix.form.clientid',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                ],
                'required'    => true,
                'constraints' => [$this->getNotBlankConstraint()],
            ]
        );

        $builder->add(
            'client_secret',
            PasswordType::class,
            [
                'label'      => 'mautic.citrix.form.clientsecret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                ],
                'required'    => false,
            ]
        );
    }
}