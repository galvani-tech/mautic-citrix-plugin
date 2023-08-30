<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

class GotomeetingIntegration extends CitrixAbstractIntegration
{
    public function getName(): string
    {
        return 'Gotomeeting';
    }

    public function getDisplayName(): string
    {
        return 'GoToMeeting';
    }
}
