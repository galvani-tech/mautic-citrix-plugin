<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticCitrixBundle\Entity\CitrixEvent;

class LoadCitrixData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        $today = new \DateTime();
        $email = 'joe.o\'connor@domain.com';

        // create a new lead
        $lead = new Lead();
        $lead->setDateAdded($today);
        $lead->setEmail($email);
        $lead->checkAttributionDate();

        $this->entityManager->persist($lead);
        $this->entityManager->flush();

        $this->setReference('lead-citrix', $lead);

        // create event
        $event = new CitrixEvent();
        $event->setLead($lead);
        $event->setEventDate($today);
        $event->setProduct('webinar');
        $event->setEmail($email);
        $event->setEventType('registered');
        $event->setEventName('sample-webinar_#0000');
        $event->setEventDesc('Sample Webinar');

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->setReference('citrix-event', $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }
}
