<?php

namespace MauticPlugin\MauticCitrixBundle\Integration;

/**
 * Class HubspotIntegration.
 */
class GototrainingIntegration extends CitrixAbstractIntegration
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Gototraining';
    }

    public function getDisplayName(): string
    {
        return 'GoToTraining';
    }
}
