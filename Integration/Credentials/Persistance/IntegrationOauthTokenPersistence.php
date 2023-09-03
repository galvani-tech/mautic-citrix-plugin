<?php declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration\Credentials\Persistance;

use kamermans\OAuth2\Persistence\ClosureTokenPersistence;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;

trait IntegrationOauthTokenPersistence
{
    public function getTokenPersistence(): TokenPersistenceInterface
    {
        // Returns true if the item exists in cache
        $exists = function () {
            return $this->getApiKeys()['access_token'] ?? false;
        };

        // Sets the given $value array in cache
        $set = function (array $value) {
            $apiKeys = array_merge($this->getApiKeys(), $value);
            $this->setApiKeys($apiKeys);
        };

        // Gets the previously-stored value from cache (or null)
        $get = function () {
            $authKeys = array_intersect_key($this->getApiKeys(), array_flip(['access_token', 'refresh_token', 'expires']));
            return $authKeys === [] ? null : $authKeys;
        };

        // Deletes the previously-stored value from cache (if exists)
        $delete = function () {
            $apiKeys = array_diff_key($this->getApiKeys(), array_flip(['access_token', 'refresh_token', 'expires']));
            $this->setApiKeys($apiKeys);
        };

        return new ClosureTokenPersistence($set, $get, $delete, $exists);
    }
}