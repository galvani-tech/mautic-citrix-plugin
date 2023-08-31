<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

use Mautic\IntegrationsBundle\Auth\Provider\AuthConfigInterface;
use Mautic\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth1aThreeLegged\CredentialsInterface;
use Mautic\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials\RedirectUriInterface;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthorizeButtonInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormCallbackInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use MauticPlugin\MauticCitrixBundle\Form\Type\ConfigAuthType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CitrixAbstractIntegration.
 */
abstract class CitrixAbstractIntegration extends BasicIntegration implements IntegrationInterface, ConfigFormAuthInterface, ConfigFormAuthorizeButtonInterface, CredentialsInterface, RedirectUriInterface, ConfigFormCallbackInterface
{
    use DefaultConfigFormTrait;

    /** @var array<string> */
    protected array $apiKeys = [];

    protected ?IntegrationCredentials $credentials = null;

    public function __construct(private IntegrationsHelper $integrationsHelper, private RouterInterface $router)
    {

    }

    // @TODO move to trait ori AbstractConfigSupport to create
    public function getRedirectUri() : string{
        return $this->router->generate('mautic_integration_auth_callback', ['integration' => $this->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
 // TODO: Implement getRedirectUri() method.
}

    public function getCallbackHelpMessageTranslationKey(): string
    {
        return 'mautic.citrix.config.form.callback.help';
    }





    public function getRequestTokenUrl() : string{
        return 'aaa';
 // TODO: Implement getRequestTokenUrl() method.
}
public function getAuthCallbackUrl() : ?string{
 // TODO: Implement getAuthCallbackUrl() method.
    return 'aaa';
}
public function getConsumerId() : ?string{
 // TODO: Implement getConsumerId() method.
    return 'aaa';
}
public function getConsumerSecret() : ?string{
 // TODO: Implement getConsumerSecret() method.
    return 'aaa';
}
public function getAccessToken() : ?string{
 // TODO: Implement getAccessToken() method.
    return 'aaa';
}
public function getRequestToken() : ?string{
 // TODO: Implement getRequestToken() method.
    return 'aaa';
}




    public function getApiKeys(): array
    {
        return $this->apiKeys;
    }


    public function getCredentials(): ?IntegrationCredentials
    {
        if (null === $this->credentials) {
            $this->credentials = new IntegrationCredentials(...$this->integrationsHelper->getIntegrationConfiguration($this)->getApiKeys());
        }

        return $this->credentials;
    }


    public function isAuthorized(): bool
    {
        return false;
        $credentials = $this->getCredentials();
        false;
        // TODO: Implement isAuthorized() method.
    }

    public function getAuthorizationUrl(): string
    {
        return $this->getApiUrl() . '/oauth/v2/authorize';
    }

    public function getIcon(): string
    {
        return 'plugins/MauticEpathBundle/Assets/img/epath_logo.png';
    }

    public function getAuthConfigFormName(): string
    {
        return ConfigAuthType::class;
    }


//    public function setIntegrationConfiguration(Integration $integration) : void{
//        $this->credentials = new IntegrationCredentials(...$this->integrationsHelper->getIntegrationConfiguration($this)->getApiKeys());
//    }


//    /**
//     * {@inheritdoc}
//     *
//     * @return array
//     */
//    public function getRequiredKeyFields(): array
//    {
//        return [
//            'app_name' => 'mautic.citrix.form.appname',
//            'client_id' => 'mautic.citrix.form.clientid',
//            'client_secret' => 'mautic.citrix.form.clientsecret',
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
        return 'https://api.getgo.com';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAccessTokenUrl(): string
    {
        return $this->getApiUrl() . '/oauth/v2/token';
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        $keys = $this->getKeys();

        return $keys[$this->getAuthTokenKey()];
    }

    /**
     * @param bool $inAuthorization
     *
     * @return string|null
     */
    public function getBearerToken($inAuthorization = false)
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

    /**
     * Get the keys for the refresh token and expiry.
     *
     * @return array
     */
    public function getRefreshTokenKeys()
    {
        return ['refresh_token', 'expires'];
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
