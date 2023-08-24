<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\DependencyInjection;

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = ['Event', 'Entity', 'Assets', 'Api'];

    $services->load('MauticPlugin\\MauticCitrixBundle\\', '../')
        ->exclude('../{'.implode(',', [...MauticCoreExtension::DEFAULT_EXCLUDES, ...$excludes]).'}');
};
