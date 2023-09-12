<?php declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration\Traits;

use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\Exception\AccessTokenRequestException;
use Mautic\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;
use Mautic\PluginBundle\Event\PluginIntegrationAuthCallbackUrlEvent;
use Mautic\PluginBundle\Event\PluginIntegrationFormDisplayEvent;
use Mautic\PluginBundle\Exception\ApiErrorException;
use Mautic\PluginBundle\Helper\oAuthHelper;
use Mautic\PluginBundle\PluginEvents;
use MauticPlugin\MauticCitrixBundle\Integration\Credentials\OAuth2ThreeLeggedCredentials;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait OauthAuthentication
{
    abstract public function getClientIdKey(): string;

    abstract public function getClientSecretKey(): string;

    abstract public function getAuthTokenKey(): string;

    abstract public function getRequestTokenUrl(): string;

    abstract public function getBearerToken(bool $inAuthorization = false): ?string;

    abstract public function getAuthenticationType(): string;

    abstract public function getRouter(): RouterInterface;

    abstract public function getTranslator(): TranslatorInterface;

    abstract public function getDispatcher(): EventDispatcher;

    abstract public function getAuthenticationUrl(): string;

    abstract public function getRequestStack(): RequestStack;

    /**
     * Method to prepare the request parameters. Builds array of headers and parameters.
     *
     * @return array
     */
    public function prepareRequest($url, $parameters, $method, $settings, $authType)
    {
        $clientIdKey = $this->getClientIdKey();
        $clientSecretKey = $this->getClientSecretKey();
        $authTokenKey = $this->getAuthTokenKey();
        $authToken = '';
        if (isset($settings['override_auth_token'])) {
            $authToken = $settings['override_auth_token'];
        } elseif (isset($this->getApiKeys()[$authTokenKey])) {
            $authToken = $this->getApiKeys()[$authTokenKey];
        }

        // Override token parameter key if neede
        if (!empty($settings[$authTokenKey])) {
            $authTokenKey = $settings[$authTokenKey];
        }

        $headers = [];

        if (!empty($settings['authorize_session'])) {
            switch ($authType) {
                case 'oauth1a':
                    $requestTokenUrl = $this->getRequestTokenUrl();
                    if (!array_key_exists('append_callback', $settings) && !empty($requestTokenUrl)) {
                        $settings['append_callback'] = false;
                    }
                    $oauthHelper = new oAuthHelper($this, $this->getRequestStack()->getCurrentRequest(), $settings);
                    $headers = $oauthHelper->getAuthorizationHeader($url, $parameters, $method);
                    break;
                case 'oauth2':
                    if ($bearerToken = $this->getBearerToken(true)) {
                        $headers = [
                            "Authorization: Basic {$bearerToken}",
                            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                        ];
                        $parameters['grant_type'] = 'client_credentials';
                    } else {
                        $defaultGrantType = (!empty($settings['refresh_token'])) ? 'refresh_token'
                            : 'authorization_code';
                        $grantType = (!isset($settings['grant_type'])) ? $defaultGrantType
                            : $settings['grant_type'];

                        $useClientIdKey = (empty($settings[$clientIdKey])) ? $clientIdKey : $settings[$clientIdKey];
                        $useClientSecretKey = (empty($settings[$clientSecretKey])) ? $clientSecretKey
                            : $settings[$clientSecretKey];
                        $parameters = array_merge(
                            $parameters,
                            [
                                $useClientIdKey => $this->getApiKeys()[$clientIdKey],
                                $useClientSecretKey => isset($this->getApiKeys()[$clientSecretKey]) ? $this->getApiKeys()[$clientSecretKey] : '',
                                'grant_type' => $grantType,
                            ]
                        );

                        if (!empty($settings['refresh_token']) && !empty($this->getApiKeys()[$settings['refresh_token']])) {
                            $parameters[$settings['refresh_token']] = $this->getApiKeys()[$settings['refresh_token']];
                        }

                        if ('authorization_code' == $grantType) {
                            $parameters['code'] = $this->getRequestStack()->getCurrentRequest()->get('code');
                        }
                        if (empty($settings['ignore_redirecturi'])) {
                            $callback = $this->getAuthCallbackUrl();
                            $parameters['redirect_uri'] = $callback;
                        }
                    }
                    break;
            }
        } else {
            switch ($authType) {
                case 'basic':
                    $headers = [
                        'Authorization' => 'Basic ' . base64_encode($this->getApiKeys()['username'] . ':' . $this->getApiKeys()['password']),
                    ];
                    break;
                case 'oauth1a':
                    $oauthHelper = new oAuthHelper($this, $this->getRequestStack()->getCurrentRequest(), $settings);
                    $headers = $oauthHelper->getAuthorizationHeader($url, $parameters, $method);
                    break;
                case 'oauth2':
                    if ($bearerToken = $this->getBearerToken()) {
                        $headers = [
                            "Authorization: Bearer {$bearerToken}",
                            // "Content-Type: application/x-www-form-urlencoded;charset=UTF-8"
                        ];
                    } else {
                        if (!empty($settings['append_auth_token'])) {
                            // Workaround because $settings cannot be manipulated here
                            $parameters['append_to_query'] = [
                                $authTokenKey => $authToken,
                            ];
                        } else {
                            $parameters[$authTokenKey] = $authToken;
                        }

                        $headers = [
                            "oauth-token: $authTokenKey",
                            "Authorization: OAuth {$authToken}",
                        ];
                    }
                    break;
                case 'key':
                    $parameters[$authTokenKey] = $authToken;
                    break;
            }
        }

        return [$parameters, $headers];
    }

    /**
     * Generate the auth login URL.  Note that if oauth2, response_type=code is assumed.  If this is not the case,
     * override this function.
     *
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        $authType = $this->getAuthenticationType();

        if ('oauth2' == $authType) {
            $callback = $this->getAuthCallbackUrl();
            $clientIdKey = $this->getClientIdKey();
            $state = $this->getAuthLoginState();
            $url = $this->getAuthenticationUrl()
                . '?client_id=' . $this->getApiKeys()[$clientIdKey]
                . '&response_type=code'
                . '&redirect_uri=' . urlencode($callback)
                . '&state=' . $state;

            if ($scope = $this->getAuthScope()) {
                $url .= '&scope=' . urlencode($scope);
            }

            if ($this->getRequestStack()->getSession()) {
                $this->getRequestStack()->getSession()->set($this->getName() . '_csrf_token', $state);
            }

            return $url;
        } else {
            return $this->getRouter()->generate(
                'mautic_integration_auth_callback',
                ['integration' => $this->getName()]
            );
        }
    }

    /**
     * State variable to append to login url (usually used in oAuth flows).
     *
     * @return string
     */
    public function getAuthLoginState(): string
    {
        return hash('sha1', uniqid((string)mt_rand()));
    }

    /**
     * Get the scope for auth flows.
     *
     * @return string
     */
    public function getAuthScope()
    {
        return '';
    }

    /**
     * Gets the URL for the built in oauth callback.
     *
     * @return string
     */
    public function getAuthCallbackUrl()
    {
        $defaultUrl = $this->getRouter()->generate(
            'mautic_integration_auth_callback',
            ['integration' => $this->getName()],
            UrlGeneratorInterface::ABSOLUTE_URL // absolute
        );

        /** @var PluginIntegrationAuthCallbackUrlEvent $event */
        $event = $this->getDispatcher()->dispatch(
            new PluginIntegrationAuthCallbackUrlEvent($this, $defaultUrl),
            PluginEvents::PLUGIN_ON_INTEGRATION_GET_AUTH_CALLBACK_URL
        );

        return $event->getCallbackUrl();
    }

    /**
     * Retrieves and stores tokens returned from oAuthLogin.
     *
     * @param array $settings
     * @param array $parameters
     *
     * @return bool|string false if no error; otherwise the error string
     *
     * @throws ApiErrorException if OAuth2 state does not match
     */
    public function authCallback($settings = [], $parameters = [])
    {
        $authType = $this->getAuthenticationType();

        switch ($authType) {
            case 'oauth2':
                if ($this->getRequestStack()->getSession() && MAUTIC_ENV !== 'dev') {
                    $state = $this->getRequestStack()->getSession()->get($this->getName() . '_csrf_token', false);
                    //$givenState = ($this->getRequestStack()->getCurrentRequest()->isXmlHttpRequest()) ? $this->getRequestStack()->getCurrentRequest()->request->get('state') : $this->getRequestStack()->getCurrentRequest()->get('state');

                    if ($state && $state !== $this->getRequestStack()->getCurrentRequest()->get('state')) {
                        $this->getRequestStack()->getSession()->remove($this->getName() . '_csrf_token');
                        throw new ApiErrorException($this->translator->trans('mautic.integration.auth.invalid.state'));
                    }
                }

                if (($settings['use_refresh_token'] ?? false)) {
                    // Try refresh token
                    $refreshTokenKeys = $this->getRefreshTokenKeys();

                    if (!empty($refreshTokenKeys)) {
                        [$refreshTokenKey, $expiryKey] = $refreshTokenKeys;

                        $settings['refresh_token'] = $refreshTokenKey;
                    }
                }
                break;


        }
        $request = $this->getRequestStack()->getCurrentRequest();

        /* @var OAuth2ThreeLeggedCredentials $credentials */
        $credentials = $this->getCredentials();
        $credentials->setCode($request->get('code'));
        $credentials->setState($request->get('state'));

        //  Remove standing api keys
        $this->setApiKeys(array_intersect_key($this->getApiKeys(), array_flip(['app_name', 'client_id', 'client_secret'])));

        //  remove oauth token and refresh token
        $credentials->setAccessToken(null)->setRefreshToken(null);

        $client = $this->httpFactory->getClient($credentials, $this);
        try {
            $client->get('https://api.getgo.com/G2M/rest/historicalMeetings', [
                'query' => [
                    'startDate' => (new \DateTimeImmutable('2020-01-01'))->format('c'),
                    'endDate' => (new \DateTimeImmutable('2020-01-01'))->format('c'),
                ],
            ]);
        } catch (AccessTokenRequestException $exception) {
            // @TODO logger
            throw new \InvalidArgumentException($this->getTranslator()->trans($exception->getMessage()));
        } catch (ClientException $exception) {
            throw new \InvalidArgumentException($this->getTranslator()->trans($exception->getMessage()));
        }

        return false;
    }

    /**
     * Called in extractAuthKeys before key comparison begins to give opportunity to set expiry, rename keys, etc.
     *
     * @return mixed
     */
    public function prepareResponseForExtraction($data)
    {
        return $data;
    }

    public function getFormSettings()
    {
        $type = $this->getAuthenticationType();
        $enableDataPriority = $this->getDataPriority();
        switch ($type) {
            case 'oauth1a':
            case 'oauth2':
                $callback = true;
                $requiresAuthorization = true;
                break;
            default:
                $callback = false;
                $requiresAuthorization = false;
                break;
        }

        return [
            'requires_callback' => $callback,
            'requires_authorization' => $requiresAuthorization,
            'default_features' => [],
            'enable_data_priority' => $enableDataPriority,
        ];
    }

    /**
     * @return array
     */
    public function getFormDisplaySettings()
    {
        /** @var PluginIntegrationFormDisplayEvent $event */
        $event = $this->dispatcher->dispatch(
            new PluginIntegrationFormDisplayEvent($this, $this->getFormSettings()),
            PluginEvents::PLUGIN_ON_INTEGRATION_FORM_DISPLAY
        );

        return $event->getSettings();
    }

    public function getCredentials(): AuthCredentialsInterface
    {
        switch ($this->getAuthenticationType()) {
            case 'oauth2':
            default:
                //  get rid of all these getters, split keys into user entered and oauth2 server returned
                return new OAuth2ThreeLeggedCredentials(
                    $this->getApiKeys()[$this->getClientIdKey()],
                    $this->getApiKeys()[$this->getClientSecretKey()],
                    $this->getApiKeys()[$this->getAuthTokenKey()],
                    $this->getApiKeys()[$this->getRefreshTokenKey()],
                    $this->getRedirectUri(),
                    $this->getAuthorizationUrl(),
                    $this->getAccessTokenUrl(),
                    $this->getApiUrl(),
                );
        }
    }
}