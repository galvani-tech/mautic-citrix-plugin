<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Helper;

use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\MauticCitrixBundle\Api\GotoassistApi;
use MauticPlugin\MauticCitrixBundle\Api\GotomeetingApi;
use MauticPlugin\MauticCitrixBundle\Api\GototrainingApi;
use MauticPlugin\MauticCitrixBundle\Api\GotowebinarApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CitrixHelper
{
    private static ?\Psr\Log\LoggerInterface $logger = null;

    private static ?\Mautic\PluginBundle\Helper\IntegrationHelper $integrationHelper = null;

    private static ?\Symfony\Component\Routing\RouterInterface $router = null;

    public static function init(IntegrationHelper $helper, LoggerInterface $logger, RouterInterface $router): void
    {
        self::$logger            = $logger;
        self::$integrationHelper = $helper;
        self::$router            = $router;
    }

    /**
     * Get the API helper.
     *
     * @return GotomeetingApi
     */
    public static function getG2mApi()
    {
        static $g2mapi;
        if (null === $g2mapi) {
            $class  = '\\'. GotomeetingApi::class;
            $g2mapi = new $class(self::getIntegration('Gotomeeting'));
        }

        return $g2mapi;
    }

    /**
     * Get the API helper.
     *
     * @return GotowebinarApi
     */
    public static function getG2wApi()
    {
        static $g2wapi;
        if (null === $g2wapi) {
            $class  = '\\'. GotowebinarApi::class;
            $g2wapi = new $class(self::getIntegration('Gotowebinar'));
        }

        return $g2wapi;
    }

    /**
     * Get the API helper.
     *
     * @return GototrainingApi
     */
    public static function getG2tApi()
    {
        static $g2tapi;
        if (null === $g2tapi) {
            $class  = '\\'. GototrainingApi::class;
            $g2tapi = new $class(self::getIntegration('Gototraining'));
        }

        return $g2tapi;
    }

    /**
     * Get the API helper.
     *
     * @return GotoassistApi
     */
    public static function getG2aApi()
    {
        static $g2aapi;
        if (null === $g2aapi) {
            $class  = '\\'. GotoassistApi::class;
            $g2aapi = new $class(self::getIntegration('Gotoassist'));
        }

        return $g2aapi;
    }

    /**
     * @deprecated
     * @param string $level
     */
    public static function log($msg, $level = 'error'): void
    {
        /** @deprecated  */
        try {
            self::$logger->log($level, $msg);
        } catch (\Exception) {
            // do nothing
        }
    }

    /**
     * @param array $results
     *
     * @return \Generator
     */
    public static function getKeyPairs($results, $key, $value)
    {
        /** @var array $results */
        foreach ($results as $result) {
            if (array_key_exists($key, $result) && array_key_exists($value, $result)) {
                yield $result[$key] => $result[$value];
            }
        }
    }

    /**
     * @param array $sessions
     * @param bool  $showAll  Wether or not to show only active sessions
     *
     * @return \Generator
     */
    public static function getAssistPairs($sessions, $showAll = false)
    {
        /** @var array $sessions */
        foreach ($sessions as $session) {
            if ($showAll || !in_array($session['status'], ['notStarted', 'abandoned'], true)) {
                yield $session['sessionId'] => sprintf('%s (%s)', $session['sessionId'], $session['status']);
            }
        }
    }

    public static function getCleanString(string $str, int $limit = 20): string
    {
        // Lowercase the string and convert HTML entities to UTF-8
        $str = htmlentities(mb_strtolower($str), ENT_NOQUOTES, 'utf-8');

        // Translate foreign characters to base characters
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str); // remove anything not already replaced

        // Replace any non-letter/digit with '-', but avoid consecutive '-'.
        $str = preg_replace('/[^a-z0-9]+/', '-', $str);

        // Trim and limit the string size
        $str = trim($str, '-');
        $str = substr($str, 0, $limit);
        $str = rtrim($str, '-');

        return $str;
    }

    /**
     * @deprecated
     * @param string $product
     * @param string $productId
     *
     * @return array
     *
     * @throws \Mautic\PluginBundle\Exception\ApiErrorException
     */
    public static function getRegistrants($product, $productId)
    {
        throw new \Exception('Deprecated');

        $result = [];
        switch ($product) {
            case CitrixProducts::GOTOWEBINAR:
                $result = self::getG2wApi()->request($product.'s/'.$productId.'/registrants');
                break;

            case CitrixProducts::GOTOTRAINING:
                $result = self::getG2tApi()->request($product.'s/'.$productId.'/registrants');
                break;
        }

        return self::extractContacts($result);
    }

    /**
     * @deprecated
     * @param string $product
     * @param string $productId
     *
     * @return array
     *
     * @throws \Mautic\PluginBundle\Exception\ApiErrorException
     */
    public static function getAttendees($product, $productId)
    {
        throw new \Exception('Deprecated');

        $result = [];
        switch ($product) {
            case CitrixProducts::GOTOWEBINAR:
                $result = self::getG2wApi()->request($product.'s/'.$productId.'/attendees');
                break;

            case CitrixProducts::GOTOMEETING:
                $result = self::getG2mApi()->request($product.'s/'.$productId.'/attendees');
                break;

            case CitrixProducts::GOTOTRAINING:
                $reports  = self::getG2tApi()->request($product.'s/'.$productId, [], 'GET', 'rest/reports');
                $sessions = array_column($reports, 'sessionKey');
                foreach ($sessions as $session) {
                    $result = self::getG2tApi()->request(
                        'sessions/'.$session.'/attendees',
                        [],
                        'GET',
                        'rest/reports'
                    );
                    $arr    = array_column($result, 'email');
                    $result = array_merge($result, $arr);
                }

                break;
        }

        return self::extractContacts($result);
    }

    public static function extractContacts($results)
    {
        $contacts = [];

        foreach ($results as $result) {
            $emailKey = false;
            if (isset($result['attendeeEmail'])) {
                if (empty($result['attendeeEmail'])) {
                    // ignore
                    continue;
                }
                $emailKey = strtolower($result['attendeeEmail']);
                $names    = explode(' ', $result['attendeeName']);
                switch (count($names)) {
                    case 1:
                        $firstname = $names[0];
                        $lastname  = '';
                        break;
                    case 2:
                        [$firstname, $lastname] = $names;
                        break;
                    default:
                        $firstname = $names[0];
                        unset($names[0]);
                        $lastname = implode(' ', $names);
                }

                $contacts[$emailKey] = [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $result['attendeeEmail'],
                ];
            } elseif (!empty($result['email'])) {
                $emailKey            = strtolower($result['email']);
                $contacts[$emailKey] = [
                    'firstname' => $result['firstName'] ?? '',
                    'lastname'  => $result['lastName'] ?? '',
                    'email'     => $result['email'],
                    'joinUrl'   => $result['joinUrl'] ?? '',
                ];
            }

            if ($emailKey) {
                $eventDate = null;
                // Extract join/register time
                if (!empty($result['attendance']['joinTime'])) {
                    $eventDate = $result['attendance']['joinTime'];
                } elseif (!empty($result['joinTime'])) {
                    $eventDate = $result['joinTime'];
                } elseif (!empty($result['inSessionTimes']['joinTime'])) {
                    $eventDate = $result['inSessionTimes']['joinTime'];
                } elseif (!empty($result['registrationDate'])) {
                    $eventDate = $result['registrationDate'];
                }

                if ($eventDate) {
                    $contacts[$emailKey]['event_date'] = new \DateTime($eventDate);
                }
            }
        }

        return $contacts;
    }
}
