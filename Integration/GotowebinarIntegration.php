<?php

namespace MauticPlugin\MauticCitrixBundle\Integration;

/**
 * Class HubspotIntegration.
 */
class GotowebinarIntegration extends CitrixAbstractIntegration
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Gotowebinar';
    }

    public function getDisplayName(): string
    {
        return 'GoToWebinar';
    }
}
