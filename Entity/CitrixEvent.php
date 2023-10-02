<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;

#[ORM\Entity]
#[ORM\Table(name: 'plugin_citrix_events')]
class CitrixEvent
{
    #[ORM\Column(name: 'id', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @var Lead
     */
    protected $lead;

    #[ORM\Column(name: 'product', type: \Doctrine\DBAL\Types\Types::STRING, length: 20)]
    protected ?string $product = 'undefined';

    #[ORM\Column(name: 'email', type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    protected ?string $email = 'undefined';

    #[ORM\Column(name: 'event_name', type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    protected ?string $eventName = 'undefined';

    #[ORM\Column(name: 'event_desc', type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    protected ?string $eventDesc = 'undefined';

    #[ORM\Column(name: 'event_type', type: \Doctrine\DBAL\Types\Types::STRING, length: 50)]
    protected ?string $eventType = 'undefined';

    #[ORM\Column(name: 'event_date', type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $eventDate = null;

    public function __construct()
    {
        $this->eventDate = new \DateTime();
    }

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('plugin_citrix_events')
            ->setCustomRepositoryClass(CitrixEventRepository::class)
            ->addIndex(['product', 'email'], 'citrix_event_email')
            ->addIndex(['product', 'event_name', 'event_type'], 'citrix_event_name')
            ->addIndex(['product', 'event_type', 'event_date'], 'citrix_event_type')
            ->addIndex(['product', 'email', 'event_type'], 'citrix_event_product')
            ->addIndex(['product', 'email', 'event_type', 'event_name'], 'citrix_event_product_name')
            ->addIndex(['product', 'event_type', 'event_name', 'lead_id'], 'citrix_event_product_name_lead')
            ->addIndex(['product', 'event_type', 'lead_id'], 'citrix_event_product_type_lead')
            ->addIndex(['event_date'], 'citrix_event_date');
        $builder->addId();
        $builder->addNamedField('product', 'string', 'product');
        $builder->addNamedField('email', 'string', 'email');
        $builder->addNamedField('eventName', 'string', 'event_name');
        $builder->addNamedField('eventDesc', 'string', 'event_desc', true);
        $builder->createField('eventType', 'string')
            ->columnName('event_type')
            ->length(50)
            ->build();
        $builder->addNamedField('eventDate', 'datetime', 'event_date');
        $builder->addLead();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    public function getEventNameOnly(): string
    {
        $eventName = $this->eventName;

        return substr($eventName, 0, strpos($eventName, '_#'));
    }

    public function getEventId(): string
    {
        $eventName = $this->eventName;

        return substr($eventName, strpos($eventName, '_#') + 2);
    }

    /**
     * @return string
     */
    public function getEventDesc()
    {
        $pos = strpos($this->eventDesc, '_!');
        if (false === $pos) {
            return $this->eventDesc;
        }

        return substr($this->eventDesc, 0, $pos);
    }

    public function getJoinUrl(): string
    {
        $pos = strpos($this->eventDesc, '_!');
        if (false === $pos) {
            return '';
        }

        return substr($this->eventDesc, $pos + 2);
    }

    /**
     * @return $this
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @return $this
     */
    public function setEventDate(\DateTime $eventDate)
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return $this
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * @return $this
     */
    public function setEventDesc($eventDesc)
    {
        $this->eventDesc = $eventDesc;

        return $this;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @return CitrixEvent
     */
    public function setLead(Lead $lead)
    {
        $this->lead = $lead;

        return $this;
    }
}
