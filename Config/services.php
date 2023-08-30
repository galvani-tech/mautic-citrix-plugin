<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\DependencyInjection;

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use MauticPlugin\MauticCitrixBundle\Integration\CitrixAbstractIntegration;
use MauticPlugin\MauticCitrixBundle\Integration\GotomeetingConfig;
use MauticPlugin\MauticCitrixBundle\Integration\GotomeetingIntegration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

//    $services
//        ->instanceof(CitrixAbstractIntegration::class)
//        ->tag('mautic.integration')
//        ->tag('mautic.basic_integration');

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = ['Event', 'Entity', 'Assets', 'Api', 'Integration'];

    $services->load('MauticPlugin\\MauticCitrixBundle\\', '../')
        ->exclude('../{' . implode(',', [...MauticCoreExtension::DEFAULT_EXCLUDES, ...$excludes]) . '}');
};
