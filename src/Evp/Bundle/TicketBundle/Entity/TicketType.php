<?php
/**
 * TicketType Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;

/**
 * TicketType
 *
 * @ORM\Table(name="evp_ticket_types")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\TicketTypeRepository")
 * @ORM\HasLifecycleCallbacks
 * @GEDMO\TranslationEntity(class="Evp\Bundle\TicketBundle\Entity\Translation")
 */
class TicketType
{
    const LABEL_TICKETS_COUNT = 'entity.ticket_type.tickets_count';
    const LABEL_NAME = 'entity.ticket_type.name';
    const LABEL_DESCRIPTION = 'entity.ticket_type.description';
    const LABEL_EVENT = 'entity.ticket_type.event_name';
    const LABEL_DISCOUNT_TYPES = 'entity.ticket_type.discount_types';
    const LABEL_LOCALE = 'admin.entity.locale.general_label';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @TicketAdmin\ListedColumn("admin.index.entity.id")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="tickets_count", type="integer", nullable=true)
     * @TicketAdmin\ListedColumn("entity.ticket_type.tickets_count")
     */
    private $ticketsCount;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @TicketAdmin\ListedColumn("entity.ticket_type.name")
     */
    private $name;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="description", type="string", length=2000, nullable=true)
     * @TicketAdmin\ListedColumn("entity.ticket_type.description")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.entity.date_created")
     */
    private $dateCreated;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Ticket", mappedBy="ticketType")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $tickets;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Event", inversedBy="ticketTypes")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("entity.ticket_type.event_name")
     */
    private $event;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", scale=4, precision=10, nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.entity.price")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Step\OrderDetails", mappedBy="ticketType")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $orderDetails;

    /**
     * @var int
     *
     * @ORM\ManyToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\DiscountType", mappedBy="ticketTypes", cascade={"all"})
     */
    private $discountTypes;

    /**
     * @var boolean
     * @ORM\Column(name="status", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("admin.status.general_label")
     */
    private $status;

    /**
     * @var string
     * @GEDMO\Locale
     */
    private $locale;

    private $discountTypesChanges;

    /**
     * @var \stdClass
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Seat\Area")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $area;

    /**
     * Constructs array collections.
     */
    public function __construct() {
        $this->tickets = new ArrayCollection();
        $this->discountTypes = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDiscountTypesChanges()
    {
        return $this->discountTypesChanges;
    }

    /**
     * @param mixed $discountTypesChanges
     *
     * @return DiscountType
     */
    public function setDiscountTypesChanges($discountTypesChanges)
    {
        $this->discountTypesChanges = $discountTypesChanges;
        return $this;
    }

    /**
     * @param \stdClass $area
     * @return self
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $discountTypes
     * @return self
     */
    public function setDiscountTypes($discountTypes)
    {
        $this->discountTypes = $discountTypes;

        return $this;
    }

    /**
     * @return DiscountType[]
     */
    public function getDiscountTypes()
    {
        return $this->discountTypes;
    }

    /**
     * @param DiscountType $discountType
     *
     * @return TicketType
     */
    public function addDiscountType($discountType)
    {
        if (!$this->discountTypes->contains($discountType)) {
            $this->discountTypes->add($discountType);
            $discountType->addTicketType($this);
        }

        return $this;
    }

    /**
     * @param DiscountType $discountType
     *
     * @return TicketType
     */
    public function removeDiscountType($discountType)
    {
        if ($this->discountTypes->removeElement($discountType)) {
            $discountType->removeTicketType($this);
        }

        return $this;
    }

    /**
     * @param mixed $orderDetails
     * @return self
     */
    public function setOrderDetails($orderDetails)
    {
        $this->orderDetails = $orderDetails;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderDetails()
    {
        return $this->orderDetails;
    }

    /**
     * @param float $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $event
     * @return self
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $dateCreated
     * @return self
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @var int
     *
     * @param $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return stripslashes($this->description);
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return stripslashes($this->name);
    }

    /**
     * @param int $tickets
     * @return self
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * @return int
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param int $ticketsCount
     * @return self
     */
    public function setTicketsCount($ticketsCount)
    {
        $this->ticketsCount = $ticketsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketsCount()
    {
        return $this->ticketsCount;
    }

    /**
     * @ORM\PrePersist
     *
     */
    public function prePersist()
    {
        $this->dateCreated = new \DateTime;
    }

    /**
     * @Assert\True(message = "message.error.tickets_count_negative")
     */
    public function isTicketsCountPositive() {
        if (intval($this->ticketsCount) < 0 ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Assert\True(message = "message.error.tickets_count_not_integer")
     */
    public function isTicketsCountInteger() {
        if (!is_numeric($this->ticketsCount) && $this->ticketsCount !== null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Assert\True(message = "message.error.ticket_price_negative")
     */
    public function isTicketPricePositive() {
        if (intval($this->price) < 0 ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Assert\True(message = "message.error.ticket_price_not_integer")
     */
    public function isTicketPriceInteger() {
        if (!is_numeric($this->price)) {
            return false;
        } else {
            return true;
        }
    }
}
