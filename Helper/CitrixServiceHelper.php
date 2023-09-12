<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Helper;

use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use MauticPlugin\MauticCitrixBundle\Integration\CitrixAbstractIntegration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

class CitrixServiceHelper
{
    public function __construct(
        private IntegrationsHelper $integrationsHelper,
        private RouterInterface $router,
        private LoggerInterface $logger,
    ) {}

    public function isIntegrationAuthorized($integrationName): bool {
        $integration = $this->integrationsHelper->getIntegration($integrationName);
        return $integration->getIntegrationConfiguration()->isPublished();
    }
}
