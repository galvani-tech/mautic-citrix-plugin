<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FormFieldSelectType.
 */
class CitrixListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'placeholder',
            TextType::class,
            [
                'label'      => 'mautic.form.field.form.emptyvalue',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $default = false;

        if (!empty($options['parentData'])) {
            $default = !empty($options['parentData']['properties']['multiple']);
        }

        $builder->add(
            'multiple',
            YesNoButtonGroupType::class,
            [
                'label'    => 'mautic.form.field.form.multiple',
                'data'     => $default,
                'required' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'parentData' => [],
            ]
        );
    }
}
