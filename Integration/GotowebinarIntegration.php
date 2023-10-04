<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\Exception\AccessTokenRequestException;
use Mautic\IntegrationsBundle\Integration\BC\BcIntegrationSettingsTrait;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Mautic\PluginBundle\Exception\ApiErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Contracts\Translation\TranslatorInterface;

// It is funny as everywhere ::NAME is used but PluginBundle just uses the class name instead
class GotowebinarIntegration extends AbstractGotoIntegration
{
    public const NAME = 'Gotowebinar';  //  this is purposely set to previous citrix name to avoid breaking changes
    public const DISPLAY_NAME = 'Goto Webinar';
    public const GOTO_PRODUCT_NAME = 'webinar';

    public function __construct(
        GotoWebinarConfiguration $configuration,
        RequestStack              $requestStack,
        TranslatorInterface       $translator,
        LoggerInterface           $logger,
    )
    {
        parent::__construct($configuration, $requestStack, $translator, $logger);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    public function getIcon(): string
    {
        return 'plugins/MauticCitrixBundle/Assets/img/goto_webinar.png';
    }

}
