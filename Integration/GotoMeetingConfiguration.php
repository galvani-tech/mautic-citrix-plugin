<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

use kamermans\OAuth2\Persistence\ClosureTokenPersistence;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface as KamermansTokenPersistenceInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials\CredentialsInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\HttpFactory;
use Mautic\IntegrationsBundle\Auth\Support\Oauth2\ConfigAccess\ConfigTokenPersistenceInterface;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\PluginBundle\Entity\Integration;
use MauticPlugin\MauticCitrixBundle\Integration\Auth\OAuth2ThreeLeggedCredentials;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GotoMeetingConfiguration extends AbstractGotoConfiguration
{
    public function getApiUrl(): string
    {
        return 'https://api.getgo.com/G2M/rest/';
    }

    protected function getIntegrationName(bool $displayName = false): string
    {
        return $displayName ? GotomeetingIntegration::DISPLAY_NAME : GotomeetingIntegration::NAME;
    }
}