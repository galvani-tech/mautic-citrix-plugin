<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

use kamermans\OAuth2\Persistence\TokenPersistenceInterface as KamermansTokenPersistenceInterface;
use Mautic\IntegrationsBundle\Auth\Provider\AuthProviderInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials\RedirectUriInterface;
use Mautic\IntegrationsBundle\Auth\Support\Oauth2\ConfigAccess\ConfigTokenPersistenceInterface;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthorizeButtonInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormCallbackInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\MauticCitrixBundle\Form\Type\ConfigAuthType;
use MauticPlugin\MauticCitrixBundle\Integration\Credentials\Persistance\IntegrationOauthTokenPersistence;
use MauticPlugin\MauticCitrixBundle\Integration\Traits\OauthAuthentication;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CitrixAbstractIntegration.
 */
abstract class CitrixAbstractIntegration extends BasicIntegration
    implements
        ConfigFormInterface,
        ConfigFormAuthInterface,
        ConfigFormAuthorizeButtonInterface,
        RedirectUriInterface,
        ConfigFormCallbackInterface,
        ConfigTokenPersistenceInterface
{
    use DefaultConfigFormTrait, OauthAuthentication, IntegrationOauthTokenPersistence;

    public function __construct(
        private IntegrationsHelper    $integrationsHelper,
        private RouterInterface       $router,
        private EventDispatcher       $dispatcher,
        private TranslatorInterface   $translator,
        private RequestStack          $requestStack,
        private AuthProviderInterface $httpFactory,
    )
    {
    }

    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    public function getApiKeys(): array
    {
        return $this->integrationsHelper->getIntegrationConfiguration($this)->getApiKeys();
    }

    public function setApiKeys(array $keys): void
    {
        $this->integrationsHelper->getIntegrationConfiguration($this)->setApiKeys($keys);
    }

    // @TODO move to trait ori AbstractConfigSupport to create
    public function getRedirectUri(): string
    {
        return $this->router->generate('mautic_integration_auth_callback', ['integration' => $this->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
        // TODO: Implement getRedirectUri() method.
    }

    public function getCallbackHelpMessageTranslationKey(): string
    {
        return 'mautic.citrix.config.form.callback.help';
    }

    // TODO get rid of
    public function getClientIdKey(): string
    {
        return 'client_id';
    }

    public function getClientSecretKey(): string
    {
        return 'client_secret';

    }

    public function getAuthTokenKey(): string
    {
        return 'access_token';
    }

    public function getRequestTokenUrl(): string
    {
        return $this->getApiUrl() . '/oauth/token';
    }

    public function getAuthenticationType(): string
    {
        return 'oauth2';
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    public function getAuthenticationUrl(): string
    {
        return $this->getApiUrl() . '/oauth/authorize';
    }


    // @TODO add to BC trait and interface
    public function getUserData()
    {
        return [];
    }

    public function isAuthorized(): bool
    {
        return false;
        return
            $this->hasIntegrationConfiguration() &&
            $this->getIntegrationConfiguration()->isPublished() &&
            $this->getApiKeys()['access_token'] !== null;
    }

    public function getAuthorizatsssionUrl(): string
    {
//        $parameters = [
//            'client_id' => $this->getApiKeys()['client_id'],
//            'redirect_uri' => $this->getRedirectUri(),
//            'response_type' => 'code',
//        ];
//
//        return $this->getApiUrl() . ' / oauth / v2 / authorize ? ' . http_build_query($parameters);
        return $this->getAuthLoginUrl();
    }

    public function getIcon(): string
    {
        return 'plugins / MauticEpathBundle / Assets / img / epath_logo . png';
    }

    public function getAuthConfigFormName(): string
    {
        return ConfigAuthType::class;
    }

    public function authCallbackX(Request $request)
    {
        $accessToken = $request->get('code', null);
        if (null === $accessToken) {
            return 'mautic . citrix . error . oauthfail';
        }
        $currentKeys = $this->getApiKeys();
        $currentKeys['access_token'] = $accessToken;

        $configuration = $this->getIntegrationConfiguration()->setApiKeys($currentKeys);
        $this->integrationsHelper->saveIntegrationConfiguration($configuration);

    }

//public function setIntegrationConfiguration(Integration $integration) : void{
//       $this->apiKeys = $this->integrationsHelper->getIntegrationConfiguration($this)->getApiKeys();
//}


//    /**
//     * {@inheritdoc}
//     *
//     * @return array
//     */
//    public function getRequiredKeyFields(): array
//    {
//        return [
//            'app_name' => 'mautic . citrix . form . appname',
//            'client_id' => 'mautic . citrix . form . clientid',
//            'client_secret' => 'mautic . citrix . form . clientsecret',
//        ];
//    }

    /**
     * {@inheritdoc}
     */
    public function sortFieldsAlphabetically()
    {
        return false;
    }

    /**
     * Get the API helper.
     *
     * @return mixed
     */
    public function getApiHelper()
    {
        static $helper;
        if (null === $helper) {
            $class = '\\MauticPlugin\\MauticCitrixBundle\\Api\\' . $this->getName() . 'Api';
            $helper = new $class($this);
        }

        return $helper;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return 'https://authentication.logmeininc.com';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAccessTokenUrl(): string
    {
        return $this->getApiUrl() . '/oauth/token';
    }

    /**
     * @param bool $inAuthorization
     *
     * @return string|null
     */
    public function getBearerToken(bool $inAuthorization = false): ?string
    {
        if (!$inAuthorization && isset($this->keys[$this->getAuthTokenKey()])) {
            return $this->keys[$this->getAuthTokenKey()];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getOrganizerKey()
    {
        $keys = $this->getKeys();

        return $keys['organizer_key'];
    }

    public function getRefreshTokenKey()
    {
        return 'refresh_token';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareResponseForExtraction($data)
    {
        if (is_array($data) && isset($data['expires_in'])) {
            $data['expires'] = $data['expires_in'] + time();
        }

        return $data;
    }
}
