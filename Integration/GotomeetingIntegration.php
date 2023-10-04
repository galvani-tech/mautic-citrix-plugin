<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Integration;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
class GotomeetingIntegration extends AbstractGotoIntegration
{
    public const NAME = 'Gotomeeting';  //  this is purposely set to previous citrix name to avoid breaking changes
    public const DISPLAY_NAME = 'Goto Meeting';
    public const GOTO_PRODUCT_NAME = 'meeting';

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
        return 'plugins/MauticCitrixBundle/Assets/img/goto_meeting.png';
    }

    public function __construct(
        GotoMeetingConfiguration $configuration,
        RequestStack              $requestStack,
        TranslatorInterface       $translator,
        LoggerInterface           $logger,
    )
    {
        parent::__construct($configuration, $requestStack, $translator, $logger);
    }
}
