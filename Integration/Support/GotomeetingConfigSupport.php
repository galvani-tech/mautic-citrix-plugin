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
    use DefaultConfigFormTrait;

    public function isAuthorized() : bool{
        return false;
        $methods = get_class_methods($this);
        $keys = $this->getIntegrationConfiguration()->getApiKeys();
        dump($this->getApiKey());
 // TODO: Implement isAuthorized() method.
        return true;
}
public function getAuthorizationUrl() : string{
 // TODO: Implement getAuthorizationUrl() method.
    return 'here is the url';
}

    public function getCallbackHelpMessageTranslationKey(): string
    {
        return 'mautic.citrix.config.form.callback.help';
    }
}
