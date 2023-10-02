<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Helper;

use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\PluginBundle\Exception\ApiErrorException;
use MauticPlugin\MauticCitrixBundle\Integration\GotoMeetingConfiguration;
use MauticPlugin\MauticCitrixBundle\Integration\GotomeetingIntegration;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CitrixServiceHelper
{
    public function __construct(
        private GotoMeetingConfiguration $gotoMeetingConfiguration,
        private RouterInterface          $router,
        private LoggerInterface          $logger,
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
        return array_combine(array_column($parsed, 'meetingId'), array_column($parsed, 'subject'));
    }

    public function registerToProduct($product, $productId, $email, $firstname, $lastname): string
    {
        $config = $this->getIntegrationConfig($product);
        $client = $config->getHttpClient();

        $params = match ($product) {
            GotomeetingIntegration::GOTO_PRODUCT_NAME => [
                'email' => $email,
                'firstName' => $firstname,
                'lastName' => $lastname,
            ],
            CitrixProducts::GOTOTRAINING => [
                'email' => $email,
                'givenName' => $firstname,
                'surname' => $lastname,
            ],
            default => throw new BadRequestHttpException(sprintf('This action is not available for product %s.', $product))
        };

        $apiUrl = match ($product) {
            GotomeetingIntegration::GOTO_PRODUCT_NAME => '/webinars/' . $productId . '/registrants?resendConfirmation=true',
            CitrixProducts::GOTOTRAINING => '/trainings/' . $productId . '/registrants',
        };

        try {
            $response = $client->post($config->getApiUrl() . $apiUrl, [
                'json' => $params,
            ]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, $e->getCode());
        }

        if (!$response['joinUrl'] ?? null) {
            throw new BadRequestHttpException('Unable to register!');
        }

        $parsed = $this->parseResponse($response);

        return $parsed['joinUrl'];
    }

    public function startToProduct($product, $productId, $email, $firstname, $lastname): string
    {
        $config = $this->getIntegrationConfig($product);
        $client = $config->getHttpClient();

        try {
            switch ($product) {
                case CitrixProducts::GOTOMEETING:
                case CitrixProducts::GOTOTRAINING:
                    $path = CitrixProducts::GOTOMEETING === $product ? 'meetings' : 'trainings';
                    $response = $this->parseResponse($client->get("{$path}/{$productId}/start"));

                    return $response['hostURL'] ?? '';
                case CitrixProducts::GOTOASSIST:
                    $params = [
                        'sessionStatusCallbackUrl' => $this->router->generate('mautic_citrix_sessionchanged', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'sessionType' => 'screen_sharing',
                        'partnerObject' => '',
                        'partnerObjectUrl' => '',
                        'customerName' => "{$firstname} {$lastname}",
                        'customerEmail' => $email,
                        'machineUuid' => '',
                    ];

                    $response = $this->parseResponse($client->post('sessions', $params));

                    return $response['startScreenSharing']['launchUrl'] ?? '';
                default:
                    throw new BadRequestHttpException(sprintf('This action is not available for product %s.', $product));
            }
        } catch (\Exception $ex) {
            $this->logger->error('startProduct: ' . $ex->getMessage());
            throw new BadRequestHttpException($ex->getMessage());
        }
    }

    public function getRegistrants(string $product, string $productId)
    {
        $config = $this->getIntegrationConfig($product);
        $client = $config->getHttpClient();

        $path = match ($product) {
            CitrixProducts::GOTOWEBINAR, CitrixProducts::GOTOTRAINING => $product . 's/' . $productId . '/registrants',
            default => throw new \InvalidArgumentException("Invalid product: $product"),
        };

        $result = $client->request('GET', $path);

        return CitrixHelper::extractContacts($result);
    }

    public function getAttendees($product, $productId)
    {
        $config = $this->getIntegrationConfig($product);
        $client = $config->getHttpClient();

        $endpoint = $product . 's/' . $productId;

        $returnData = match ($product) {
            CitrixProducts::GOTOWEBINAR, CitrixProducts::GOTOMEETING => $client->request($endpoint . '/attendees'),
            CitrixProducts::GOTOTRAINING => array_merge_recursive(
                ...(array_map(fn ($session) => $client->request('sessions/' . $session . '/attendees', [], 'GET', 'rest/reports'), array_column($client->request($endpoint, [], 'GET', 'rest/reports'), 'sessionKey'))),
            ),
            default => throw new BadRequestHttpException(sprintf('This action is not available for product %s.', $product))
        };

        return CitrixHelper::extractContacts($returnData);
    }

    private function getIntegrationConfig(string $productName)
    {
        return match ($productName) {
            GotomeetingIntegration::GOTO_PRODUCT_NAME => $this->gotoMeetingConfiguration,
            default => throw new IntegrationNotFoundException(sprintf('Integration %s not found', $productName)),
        };
    }

    /**
     * TODO improve, check for more codes and handle more exceptions
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
