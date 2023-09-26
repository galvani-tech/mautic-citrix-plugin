<?php

declare(strict_types=1);

namespace MauticPlugin\GotoBundle\Integration;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\Exception\AccessTokenRequestException;
use Mautic\IntegrationsBundle\Integration\BC\BcIntegrationSettingsTrait;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Mautic\PluginBundle\Exception\ApiErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class GotomeetingIntegration implements IntegrationInterface
{
    use BcIntegrationSettingsTrait;
    use ConfigurationTrait;

    public const NAME = 'Gotomeeting';  //  this is purposely set to previous citrix name to avoid breaking changes
    public const DISPLAY_NAME = 'Goto Meeting';

    public function __construct(
        protected GotoMeetingConfiguration $configuration,
        protected RequestStack             $requestStack,
        protected TranslatorInterface      $translator,
        protected LoggerInterface          $logger,
    )
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    public function getIcon(): string
    {
        return 'plugins/GotoBundle/Assets/img/goto_meeting.png';
    }

    public function authCallback(): bool|string
    {
        $client = $this->getAuthorizedClient();
        try {
            $client->get('https://api.getgo.com/G2M/rest/historicalMeetings', [
                'query' => [
                    'startDate' => (new \DateTimeImmutable('2020-01-01'))->format('c'),
                    'endDate' => (new \DateTimeImmutable('2020-01-01'))->format('c'),
                ],
            ]);
            return false;
        } catch (AccessTokenRequestException|ClientException $exception) {  // TODO do
            $errorMessage = $this->parseEndpointError($exception);
        } catch (\Exception $exception) {
        }

        return $errorMessage ?? $exception->getMessage(); // means no error
    }

    protected function getAuthorizedClient(): ClientInterface
    {
        if ($this->requestStack->getSession() && MAUTIC_ENV !== 'dev') {
            $state = $this->requestStack->getSession()->get($this->getName() . '_csrf_token', false);

            if ($state !== $this->requestStack->getCurrentRequest()->get('state')) {
                $this->requestStack->getSession()->remove($this->getName() . '_csrf_token');
                throw new ApiErrorException('mautic.integration.auth.invalid.state'); // TODO check translation
            }
        }

        $request = $this->requestStack->getCurrentRequest();

        $credentials = $this->configuration->getCredentials();
        $credentials->setCode($request->get('code'));
        $credentials->setState($request->get('state'));

        //  remove oauth tokens
        $credentials->setAccessToken(null)->setRefreshToken(null)->setExpiresAt(null);

        $this->configuration->getTokenPersistence()->deleteToken();

        //  this call will perform token request and save token to credentials
        $client = $this->configuration->getHttpClient($credentials);

        return $client;
    }

    public function getUserData($identifier, &$socialCache): array
    {
        if (!$this->configuration->isAuthorized()) {
            return [];
        }

        return $this->configuration->getUserData();
    }

    private function parseEndpointError(AccessTokenRequestException|ClientException $errorMessage): string
    {
        preg_match('/\{(?:[^{}]|(?R))*\}/', $errorMessage->getMessage(), $matches);

        if ($matches) {
            try {
                $json = json_decode($matches[0], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $json = null;
            }
        }

        $errorKey = match ($json['error_description'] ?? null) {
            'error.auth.code.invalid.jwt' => 'mautic.integration.auth.invalid.jwt',
            default => 'mautic.integration.auth.error.generic'
        };
        $errorKey = match ($json['int_err_code'] ?? null) {
            'InvalidToken' => 'mautic.integration.auth.invalid.jwt',
            default => $errorKey
        };

        return $this->translator->trans($errorKey, [], 'flashes');
    }
}

