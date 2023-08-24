<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Helper;

abstract class CitrixProducts extends BasicEnum
{
    public const GOTOWEBINAR  = 'webinar';
    public const GOTOMEETING  = 'meeting';
    public const GOTOTRAINING = 'training';
    public const GOTOASSIST   = 'assist';
}
