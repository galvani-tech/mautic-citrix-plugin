<?php

declare(strict_types=1);    // not really worth anything here, but good practice

return [
    'name' => 'Goto Bundle',
    'description' => 'Goto products integration',
    'version' => '1.0',
    'author' => 'Webmecanik',
    'services' => [
        'integrations' => [
            'mautic.integration.gotomeeting' => [
                'class' => \MauticPlugin\MauticCitrixBundle\Integration\GotomeetingIntegration::class,
                'arguments' => [
                    'mautic.gotomeeting.configuration',
                    'request_stack',
                    'translator',
                    'monolog.logger.mautic',
                ],
                'tags' => [ // @todo tagging should be refactored to use services.php but just not working
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'mautic.integration.gotomeeting.form_config' => [
                'class' => \MauticPlugin\MauticCitrixBundle\Integration\Support\GotomeetingIntegrationFormSupport::class,
                'arguments' => [
                    'mautic.gotomeeting.configuration',
                    'request_stack',
                    'translator',
                    'monolog.logger.mautic',
                ],
                'tags' => ['mautic.config_integration'], // @todo tagging should be refactored to use services.php
            ],
            'mautic.integration.gotowebinar' => [
                'class' => \MauticPlugin\MauticCitrixBundle\Integration\GotowebinarIntegration::class,
                'arguments' => [
                    'mautic.gotowebinar.configuration',
                    'request_stack',
                    'translator',
                    'monolog.logger.mautic',
                ],
                'tags' => [ // @todo tagging should be refactored to use services.php but just not working
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'mautic.integration.gotowebinar.form_config' => [
                'class' => \MauticPlugin\MauticCitrixBundle\Integration\Support\GotowebinarIntegrationFormSupport::class,
                'arguments' => [
                    'mautic.gotowebinar.configuration',
                    'request_stack',
                    'translator',
                    'monolog.logger.mautic',
                ],
                'tags' => ['mautic.config_integration'], // @todo tagging should be refactored to use services.php
            ],
        ],
    ],
];
