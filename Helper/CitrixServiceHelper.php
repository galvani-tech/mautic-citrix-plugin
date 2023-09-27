<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Helper;

use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\PluginBundle\Exception\ApiErrorException;
use MauticPlugin\MauticCitrixBundle\Integration\GotoMeetingConfiguration;
use MauticPlugin\MauticCitrixBundle\Integration\GotomeetingIntegration;
use Psr\Http\Message\ResponseInterface;

class CitrixServiceHelper
{
    public function __construct(
        private IntegrationsHelper       $integrationsHelper,
        private GotoMeetingConfiguration $gotoMeetingConfiguration,
    )
    {
    }

    public function isIntegrationAuthorized($productName): bool
    {
        try {
            $integration = $this->getIntegrationConfig($productName);
            return $integration->isPublished();
        } catch (IntegrationNotFoundException) {
        }

        return false;
    }

    //  this should be just proxy to the integration's client, for now it does the job
    public function getCitrixChoices(string $product, bool $onlyUpcoming = true)
    {
        $config = $this->getIntegrationConfig($product);
        $client = $config->getHttpClient();
        $endpointUri = $onlyUpcoming ? 'upcomingMeetings' : 'historicalMeetings';
        $response = $client->get($config->getApiUrl() . $endpointUri);
        $parsed = $this->parseResponse($response);
        return array_combine(array_column($parsed,'meetingId'), array_column($parsed,'subject'));
    }

    private function getIntegrationConfig(string $productName)
    {
        switch ($productName) {
            case GotomeetingIntegration::GOTO_PRODUCT_NAME:
                return $this->gotoMeetingConfiguration;
            default:
                throw new IntegrationNotFoundException(sprintf('Integration %s not found', $productName));
        }
    }

    /**
     * @return array<string,string>|null
     * @throws ApiErrorException
     */
    private function parseResponse(ResponseInterface $response)
    {
        try {
            $responseData = match ($response->getStatusCode()) {
                200 => json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR),
                204 => null,
                default => throw new ApiErrorException($response->getReasonPhrase(), $response->getStatusCode()),
            };
        } catch (\JsonException $e) {
            throw new ApiErrorException($e->getMessage(), $response->getStatusCode());
        }

        return $responseData;
    }
}
