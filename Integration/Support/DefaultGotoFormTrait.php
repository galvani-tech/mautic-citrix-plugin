<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration\Support;

use MauticPlugin\MauticCitrixBundle\Form\Type\ConfigAuthType;

trait DefaultGotoFormTrait
{
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
