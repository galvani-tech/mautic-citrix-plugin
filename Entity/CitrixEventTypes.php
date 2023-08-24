<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCitrixBundle\Entity;

use MauticPlugin\MauticCitrixBundle\Helper\BasicEnum;

abstract class CitrixEventTypes extends BasicEnum
{
    // Used for querying events
    public const STARTED    = 'started';
    public const REGISTERED = 'registered';
    public const ATTENDED   = 'attended';
}
