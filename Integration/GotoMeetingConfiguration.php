<?php

declare(strict_types=1);

namespace MauticPlugin\GotoBundle\Integration;

use kamermans\OAuth2\Persistence\ClosureTokenPersistence;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface as KamermansTokenPersistenceInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials\CredentialsInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\HttpFactory;
use Mautic\IntegrationsBundle\Auth\Support\Oauth2\ConfigAccess\ConfigTokenPersistenceInterface;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\PluginBundle\Entity\Integration;
use MauticPlugin\GotoBundle\Integration\Auth\OAuth2ThreeLeggedCredentials;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GotoMeetingConfiguration implements ConfigTokenPersistenceInterface
{
    public function __construct(
        private IntegrationsHelper $helper,
        private RouterInterface    $router,
        private RequestStack       $requestStack,
        private HttpFactory        $httpFactory,
    )
    {
    }

    private ?array $userData = null;

    public function getHttpClient(?CredentialsInterface $credentials = null)
    {
        if (!$this->isConfigured()) {
            throw new PluginNotConfiguredException('GotoMeeting integration is not configured.');
        }

        return $this->httpFactory->getClient($credentials ?? $this->getCredentials(), $this);
    }

    public function isAuthorized(): bool
    {
        $entity = $this->getIntegrationEntity();

        if (!$entity->getIsPublished()) {
            return false;
        }

        $requiredKeys = ['client_id', 'client_secret', 'access_token', 'refresh_token', 'expires_at'];
        $apiKeys = $entity->getApiKeys();

        $filteredKeys = array_filter($apiKeys, function ($key) use ($apiKeys, $requiredKeys) {
            return in_array($key, $requiredKeys) && isset($apiKeys[$key]);
        }, ARRAY_FILTER_USE_KEY);

        return count($filteredKeys) === count($requiredKeys);
    }

    public function isConfigured(): bool
    {
        $entity = $this->getIntegrationEntity();

        $keys = $entity->getApiKeys();
        return isset($keys['client_id']) && isset($keys['client_secret']) && $entity->isPublished();
    }

    public function getIntegrationEntity(): Integration
    {
        return $this->helper->getIntegration(GotomeetingIntegration::NAME)->getIntegrationConfiguration();
    }

    public function getCredentials(): OAuth2ThreeLeggedCredentials
    {
        $apiKeys = $this->getIntegrationEntity()->getApiKeys();

        $credentialsConfig = [
            'client_id' => $apiKeys['client_id'] ?? null,
            'client_secret' => $apiKeys['client_secret'] ?? null,
            'access_token' => $apiKeys['access_token'] ?? null,
            'refresh_token' => $apiKeys['refresh_token'] ?? null,
            'expires_at' => $apiKeys['expires_at'] ?? null,
            'token_url' => $this->getTokenUrl(),
            'base_uri' => $this->getAuthenticationUrl(),
            'code' => $apiKeys['code'] ?? null,
            'state' => $apiKeys['state'] ?? null,
            'redirect_uri' => $this->router->generate('mautic_integration_auth_callback',
                ['integration' => GotomeetingIntegration::NAME],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        return new OAuth2ThreeLeggedCredentials(...$credentialsConfig);
    }

    public function getApiUrl(): string
    {
        return 'https://api.getgo.com';
    }

    public function getAuthenticationUrl(): string
    {
        return 'https://authentication.logmeininc.com';
    }

    public function getAuthorizationUrl(): string
    {
        $apiKeys = $this->getIntegrationEntity()->getApiKeys();

        $state = $this->getAuthLoginState();
        $url = $this->getAuthenticationUrl() . '/oauth/authorize'
            . '?client_id=' . $apiKeys['client_id']
            . '&response_type=code'
            . '&redirect_uri=' . urlencode($this->getCallbackUrl())
            . '&state=' . $state;

        return $url;
    }

    public function getTokenUrl(): string
    {
        return $this->getAuthenticationUrl() . '/oauth/token';
    }

    public function getCallbackUrl(): string
    {
        return $this->router->generate(
            'mautic_integration_auth_callback',
            ['integration' => GotomeetingIntegration::NAME],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    // Authentication related functions
    public function getAuthLoginState(): string
    {
        $state = hash('sha1', uniqid((string)mt_rand()));
        $session = $this->requestStack->getSession();

        $session->set(GotomeetingIntegration::NAME . '_csrf_token', $state); // TODO not working
        $session->save();

        return $state;
    }

    public function getTokenPersistence(): KamermansTokenPersistenceInterface
    {
        return new ClosureTokenPersistence(
            function (array $keys) {    // Save tokens
                $standingKeys = $this->getIntegrationEntity()->getApiKeys();
                $standingKeys['access_token'] = $keys['access_token'] ?? null;
                $standingKeys['refresh_token'] = $keys['refresh_token'] ?? null;
                $standingKeys['expires_at'] = $keys['expires_at'] ?? null;
                $configuration = $this->getIntegrationEntity();
                $configuration->setApiKeys($standingKeys);
                $this->helper->saveIntegrationConfiguration($configuration);
            },
            function (): ?array { // Restore tokens
                $keys = $this->getIntegrationEntity()->getApiKeys();
                if ($keys['access_token'] ?? null !== null) {
                    return [
                        'access_token' => $keys['access_token'] ?? null,
                        'refresh_token' => $keys['refresh_token'] ?? null,
                        'expires_at' => $keys['expires_at'] ?? null,
                    ];
                }

                return null;
            },
            function (): bool { //  Delete tokens
                $standingKeys = $this->getIntegrationEntity()->getApiKeys();
                unset($standingKeys['access_token']);
                unset($standingKeys['refresh_token']);
                unset($standingKeys['expires_at']);
                $configuration = $this->getIntegrationEntity();
                $configuration->setApiKeys($standingKeys);
                $this->helper->saveIntegrationConfiguration($configuration);

                return true;
            },
            function (): bool {
                $keys = $this->getIntegrationEntity()->getApiKeys() ?? null;

                return $keys['access_token'] ?? null !== null;
            }
        );
    }

    // User data function

    /**
     * @return array<string,string|array<string,string>>
     * @throws PluginNotConfiguredException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserData(bool $forceReload = false): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        if ($this->userData === null || $forceReload) {
            $response = $this->getHttpClient()->get($this->getApiUrl() . '/identity/v1/Users/me');
            $this->userData = json_decode($response->getBody()->getContents(), true);
        }

        return $this->userData;
    }
}