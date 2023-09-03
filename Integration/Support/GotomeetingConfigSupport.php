<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration\Support;

use Mautic\IntegrationsBundle\Auth\Provider\AuthConfigInterface;
use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthorizeButtonInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\MauticCitrixBundle\Integration\GotomeetingIntegration;

class GotomeetingConfigSupport extends GotomeetingIntegration implements ConfigFormInterface, ConfigFormFeaturesInterface, ConfigFormAuthInterface
{

}
