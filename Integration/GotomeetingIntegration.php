<?php

namespace MauticPlugin\MauticCitrixBundle\Integration;

/**
 * Class HubspotIntegration.
 */
class GotomeetingIntegration extends CitrixAbstractIntegration
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Gotomeeting';
    }

    public function getDisplayName(): string
    {
        return 'GoToMeeting';
    }
}
