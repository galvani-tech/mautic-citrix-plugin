<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

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
