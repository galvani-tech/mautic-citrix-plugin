<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration\Support;

use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthorizeButtonInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormCallbackInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\MauticCitrixBundle\Integration\GotomeetingIntegration;

/**
 * This configures the UI for the plugin's configuration page.  The form is defined in the
 * {@see DetailsType}.
 */
class GotomeetingIntegrationFormSupport extends GotomeetingIntegration implements ConfigFormInterface, ConfigFormAuthInterface, ConfigFormAuthorizeButtonInterface, ConfigFormCallbackInterface
{
    use DefaultConfigFormTrait;
    use DefaultGotoFormTrait;
}
