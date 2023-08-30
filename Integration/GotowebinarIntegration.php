<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

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
