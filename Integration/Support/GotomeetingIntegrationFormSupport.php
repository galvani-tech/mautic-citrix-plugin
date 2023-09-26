<?php

declare(strict_types=1);

namespace MauticPlugin\GotoBundle\Integration\Support;

use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthorizeButtonInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormCallbackInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\GotoBundle\Form\Type\ConfigAuthType;
use MauticPlugin\GotoBundle\Integration\GotomeetingIntegration;

/**
 * This configures the UI for the plugin's configuration page.  The form is defined in the
 * {@see DetailsType}.
 */
class GotomeetingIntegrationFormSupport
    extends GotomeetingIntegration
    implements
    ConfigFormInterface,
    ConfigFormAuthInterface,
    ConfigFormAuthorizeButtonInterface,
    ConfigFormCallbackInterface
{
    use DefaultConfigFormTrait;

    public function getAuthConfigFormName(): string
    {
        return ConfigAuthType::class;
    }

    public function isAuthorized(): bool
    {
        return $this->configuration->isAuthorized();
    }

    public function getAuthorizationUrl(): string
    {
        return $this->configuration->getAuthorizationUrl();
    }

    public function getRedirectUri(): string
    {
        return $this->configuration->getCallbackUrl();
    }

    public function getCallbackHelpMessageTranslationKey(): string
    {
        return 'mautic.goto.config.callback.help';
    }
}
