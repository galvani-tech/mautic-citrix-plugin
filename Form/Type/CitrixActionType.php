<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Form\Type;

use Mautic\EmailBundle\Form\Type\EmailListType;
use Mautic\FormBundle\Model\FieldModel;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixProducts;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixServiceHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CitrixActionType extends AbstractType
{
    public function __construct(
        private FieldModel $model,
        private CitrixServiceHelper $serviceHelper
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!(array_key_exists('attr', $options) && array_key_exists('data-product', $options['attr'])) ||
            !CitrixProducts::isValidValue($options['attr']['data-product']) ||
            !$this->serviceHelper->isIntegrationAuthorized($options['attr']['data-product'])
        ) {
            return;
        }
        $product = $options['attr']['data-product'];

        $fields  = $this->model->getSessionFields($options['attr']['data-formid']);
        $choices = [
            '' => '',
        ];

        foreach ($fields as $f) {
            if (in_array(
                $f['type'],
                array_merge(
                    ['button', 'freetext', 'captcha'],
                    array_map(
                        fn ($p): string => 'plugin.citrix.select.'.$p,
                        CitrixProducts::toArray()
                    )
                ),
                true
            )) {
                continue;
            }
            $choices[$f['label']] = $f['id'];
        }

        if (array_key_exists('data-product-action', $options['attr']) &&
            ('register' === $options['attr']['data-product-action'] ||
                'start' === $options['attr']['data-product-action'])
        ) {
            $products = [
                'form' => 'User selection from form',
            ];
            $products = array_replace($products, $this->serviceHelper->getCitrixChoices($product));

            $builder->add(
                'product',
                ChoiceType::class,
                [
                    'choices'           => array_flip($products),
                    'expanded'          => false,
                    'label_attr'        => ['class' => 'control-label'],
                    'multiple'          => false,
                    'label'             => 'plugin.citrix.'.$product.'.listfield',
                    'attr'              => [
                        'class'   => 'form-control',
                        'tooltip' => 'plugin.citrix.selectproduct.tooltip',
                    ],
                    'required'    => true,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                ]
            );
        }

        if (array_key_exists('data-product-action', $options['attr']) &&
            ('register' === $options['attr']['data-product-action'] ||
                'screensharing' === $options['attr']['data-product-action'])
        ) {
            $builder->add(
                'firstname',
                ChoiceType::class,
                [
                    'choices'           => $choices,
                    'expanded'          => false,
                    'label_attr'        => ['class' => 'control-label'],
                    'multiple'          => false,
                    'label'             => 'plugin.citrix.first_name',
                    'attr'              => [
                        'class'   => 'form-control',
                        'tooltip' => 'plugin.citrix.first_name.tooltip',
                    ],
                    'required'    => true,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                ]
            );

            $builder->add(
                'lastname',
                ChoiceType::class,
                [
                    'choices'           => $choices,
                    'expanded'          => false,
                    'label_attr'        => ['class' => 'control-label'],
                    'multiple'          => false,
                    'label'             => 'plugin.citrix.last_name',
                    'attr'              => [
                        'class'   => 'form-control',
                        'tooltip' => 'plugin.citrix.last_name.tooltip',
                    ],
                    'required'    => true,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                ]
            );
        }

        $builder->add(
            'email',
            ChoiceType::class,
            [
                'choices'           => $choices,
                'expanded'          => false,
                'label_attr'        => ['class' => 'control-label'],
                'multiple'          => false,
                'label'             => 'plugin.citrix.selectidentifier',
                'attr'              => [
                    'class'   => 'form-control',
                    'tooltip' => 'plugin.citrix.selectidentifier.tooltip',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            ]
        );

        if (array_key_exists('data-product-action', $options['attr']) &&
            ('start' === $options['attr']['data-product-action'] ||
             'screensharing' === $options['attr']['data-product-action'])
        ) {
            $defaultOptions = [
                'label'      => 'plugin.citrix.emailtemplate',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'plugin.citrix.emailtemplate_descr',
                ],
                'required' => true,
                'multiple' => false,
            ];

            if (array_key_exists('list_options', $options)) {
                if (isset($options['list_options']['attr'])) {
                    $defaultOptions['attr'] = array_merge($defaultOptions['attr'], $options['list_options']['attr']);
                    unset($options['list_options']['attr']);
                }

                $defaultOptions = array_merge($defaultOptions, $options['list_options']);
            }

            $builder->add('template', EmailListType::class, $defaultOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'citrix_submit_action';
    }
}
