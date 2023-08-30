<?php declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

class IntegrationCredentials
{
    public function __construct(
        private ?string $app_name = null,
        private ?string $client_id = null,
        private ?string $client_secret = null,
        private ?string $token_type = null,
        private ?string $access_token = null,
        private ?string $refresh_token = null,
        private ?int    $expires_in = null,
        private ?string $account_key = null,
        private ?string $email = null,
        private ?string $firstName = null,
        private ?string $lastName = null,
        private ?string $organizer_key = null,
        private ?string $version = null,
        private ?string $account_type = null,
        private ?int    $expires = null,
    )
    {
    }

    public function getAppName(): ?string
    {
        return $this->app_name;
    }

    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    public function getClientSecret(): ?string
    {
        return $this->client_secret;
    }

    public function getTokenType(): ?string
    {
        return $this->token_type;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expires_in;
    }

    public function getAccountKey(): ?string
    {
        return $this->account_key;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getOrganizerKey(): ?string
    {
        return $this->organizer_key;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getAccountType(): ?string
    {
        return $this->account_type;
    }

    public function getExpires(): ?int
    {
        return $this->expires;
    }
}
