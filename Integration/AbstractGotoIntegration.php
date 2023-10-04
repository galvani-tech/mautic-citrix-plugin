<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\Exception\AccessTokenRequestException;
use Mautic\IntegrationsBundle\Integration\BC\BcIntegrationSettingsTrait;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Mautic\PluginBundle\Exception\ApiErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Contracts\Translation\TranslatorInterface;

// It is funny as everywhere ::NAME is used but PluginBundle just uses the class name instead
abstract class AbstractGotoIntegration implements IntegrationInterface
{
    use BcIntegrationSettingsTrait;
    use ConfigurationTrait;

    private ?array $userData = null;

    public function __construct(
        protected AbstractGotoConfiguration $configuration,
        protected RequestStack              $requestStack,
        protected TranslatorInterface       $translator,
        protected LoggerInterface           $logger,
    )
    {
    }

    public function authCallback(): bool|string
    {
        try {
            $this->getAuthorizationClient();
            $this->configuration->getUserData();

            return false;
        } catch (AccessTokenRequestException|ClientException $exception) {
            $errorMessage = $this->parseAuthError($exception);
        } catch (ApiErrorException $exception) {
            $errorMessage = $this->translator->trans($exception->getMessage(), [], 'flashes');
        } catch (\Exception $exception) {
        }

        return $errorMessage ?? $exception->getMessage();
    }

    /**
     * @throws ApiErrorException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     */
    protected function getAuthorizationClient(): ClientInterface
    {
        $session = $this->requestStack->getSession();

        $currentRequest = $this->requestStack->getCurrentRequest();
        $state = $session->get($this->getName() . '_csrf_token');

        if (false && $state !== $currentRequest->get('state')) { // TODO this is not working the session is not there :-(
            $session->remove($this->getName() . '_csrf_token');

            throw new ApiErrorException('mautic.integration.auth.invalid.state');
        }

        // delete the token from the storage, currently GotoAbstractConfiguration class acts as storage as well
        // the keys are persisted directly so the getCredentials method will return empty tokens
        $this->configuration->getTokenPersistence()->deleteToken();

        // this is the only place we need the code and state
        $credentials = $this->configuration->getCredentials();
        $credentials->setCode($currentRequest->get('code'));
        $credentials->setState($currentRequest->get('state'));

        // this call will perform token request and save received tokens into token storage
        $client = $this->configuration->getHttpClient($credentials);

        return $client;
    }

    public function getUserData(): array
    {
        if (!$this->configuration->isAuthorized()) {
            return [];
        }

        return $this->configuration->getUserData();
    }

    private function parseAuthError(AccessTokenRequestException|ClientException $errorMessage): string
    {
        preg_match('/\{(?:[^{}]|(?R))*\}/', $errorMessage->getMessage(), $matches);

        if ($matches !== []) {
            try {
                $json = json_decode($matches[0], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
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

        return $this->translator->trans($errorKey, [], domain: 'flashes');
    }
}
