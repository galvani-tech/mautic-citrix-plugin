<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

class GotoWebinarConfiguration extends AbstractGotoConfiguration
{
    public function getApiUrl(): string
    {
        return 'https://api.getgo.com/G2W/rest/v2/';
    }

    public function getApiV1Url(): string
    {
        return 'https://api.getgo.com/G2W/rest/';
    }

    protected function getIntegrationName(bool $displayName = false): string
    {
        return $displayName ? GotowebinarIntegration::DISPLAY_NAME : GotowebinarIntegration::NAME;
    }
}
