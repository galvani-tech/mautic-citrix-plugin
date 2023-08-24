<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

/**
 * Class HubspotIntegration.
 */
class GotoassistIntegration extends CitrixAbstractIntegration
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Gotoassist';
    }

    public function getDisplayName(): string
    {
        return 'GoToAssist';
    }
}
