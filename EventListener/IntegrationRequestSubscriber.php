<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\EventListener;

use Mautic\PluginBundle\Event\PluginIntegrationRequestEvent;
use Mautic\PluginBundle\PluginEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo this is no longer used
 *
 * @deprecated
 */
class IntegrationRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PLUGIN_ON_INTEGRATION_REQUEST => [
                'getParameters',
                0,
            ],
        ];
    }

    /** @deprecated */
    public function getParameters(PluginIntegrationRequestEvent $requestEvent): void
    {
        if (str_contains($requestEvent->getUrl(), 'oauth/v2/token')) {
            $authorization = $this->getAuthorization($requestEvent->getParameters());
            $requestEvent->setHeaders([
                'Authorization' => sprintf('Basic %s', base64_encode($authorization)),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ]);
        }
    }

    /** @deprecated */
    private function getAuthorization(array $parameters): string
    {
        if (empty($parameters['client_id'])) {
            throw new \Exception('No client ID given.');
        }

        if (empty($parameters['client_secret'])) {
            throw new \Exception('No client secret given.');
        }

        return sprintf('%s:%s', $parameters['client_id'], $parameters['client_secret']);
    }
}
