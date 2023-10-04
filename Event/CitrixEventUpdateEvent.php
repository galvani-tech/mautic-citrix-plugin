<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use Mautic\LeadBundle\Entity\Lead;

class CitrixEventUpdateEvent extends CommonEvent
{
    private $email;

    public function __construct(
        private $product,
        private $eventName,
        private $eventDesc,
        private $eventType,
        private Lead $lead
    ) {
        $this->email = $lead->getEmail();
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return mixed
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return mixed
     */
    public function getEventDesc()
    {
        return $this->eventDesc;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }
}
