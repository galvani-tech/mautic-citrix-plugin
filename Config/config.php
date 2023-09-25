<?php

declare(strict_types=1);    // not really worth anything here, but good practice

return [
    'name' => 'webmecanik/mautic-goto-plugin',
    'description' => 'Goto products integration',
    'version' => '1.0',
    'authors' => [
        [
            'name' => 'Jan Kozak',
            'email' => 'galvani78@gmail.com',
            'role' => 'Developer',
        ],
    ],
    'services' => [
        'integrations' => [
            'mautic.integration.gotomeeting' => [
                'class' => \MauticPlugin\GotoBundle\Integration\GotoMeetingIntegration::class,
                'arguments' => [
                    'mautic.gotomeeting.configuration',
                    'request_stack',
                    'translator',
                ],
                'tags' => [ // @todo tagging should be refactored to use services.php but just not working
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'mautic.integration.gotomeeting.form_config' => [
                'class' => \MauticPlugin\GotoBundle\Integration\Support\GotoMeetingIntegrationFormSupport::class,
                'arguments' => [
                    'mautic.gotomeeting.configuration',
                    'request_stack',
                    'translator',
                ],
                'tags' => ['mautic.config_integration'], // @todo tagging should be refactored to use services.php
            ],
        ]
    ],
];
