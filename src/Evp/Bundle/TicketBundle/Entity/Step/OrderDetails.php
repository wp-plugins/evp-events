<?php
/**
 * OrderDetails Entity for TicketTypeSelection step control
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity\Step;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\TicketType;

/**
 * Class OrderDetails
 *
 * @ORM\Table(name="evp_order_details")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\OrderDetailsRepository")
 */
class OrderDetails {

    const LABEL_TICKET_TYPE = 'entity.order_details.ticket_type';
    const LABEL_TICKETS_COUNT = 'entity.order_details.tickets_count';
    const LABEL_USER_EMAIL = 'entity.order_details.user_email';
    const LABEL_EVENT = 'entity.order_details.event';

    const VALUE_NO_EVENTS = 'entity.order_details.no_valid_events';
    const VALUE_HAS_EVENTS = 'entity.order_details.has_valid_events';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \StdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\TicketType", inversedBy="orderDetails")
     * @ORM\JoinColumn(name="ticket_type_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $ticketType;

    /**
     * @var \StdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $order;

    /**
     * @var \StdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\Column(name="tickets_count", type="integer", nullable=false)
     */
    private $ticketsCount = 0;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $ticketTypeName;

    /**
     * @var string
     */
    private $ticketTypes;

    /**
     * @var float
     */
    private $price;

    /**
     * @var int
     */
    private $ticketsLeft;

    /**
     * @var \StdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $user;

    public function __construct() {
        $this->ticketType = new ArrayCollection;
    }

    /**
     * @param string $ticketTypes
     * @return self
     */
    public function setTicketTypes($ticketTypes)
    {
        $this->ticketTypes = $ticketTypes;

        return $this;
    }

    /**
     * @return string
     */
    public function getTicketTypes()
    {
        return $this->ticketTypes;
    }

    /**
     * @param int $ticketsLeft
     * @return self
     */
    public function setTicketsLeft($ticketsLeft)
    {
        $this->ticketsLeft = $ticketsLeft;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketsLeft()
    {
        return $this->ticketsLeft;
    }

    /**
     * @param \StdClass $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \StdClass
     */
    public function getUser()
    {
        return $this->user;
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
     * @param string $ticketTypeName
     * @return self
     */
    public function setTicketTypeName($ticketTypeName)
    {
        $this->ticketTypeName = $ticketTypeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTicketTypeName()
    {
        return $this->ticketTypeName;
    }

    /**
     * @param string $currency
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \StdClass $event
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
     * @param \StdClass $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \Evp\Bundle\TicketBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \StdClass $ticketType
     * @return self
     */
    public function setTicketType($ticketType)
    {
        $this->ticketType = $ticketType;

        return $this;
    }

    /**
     * @return TicketType
     */
    public function getTicketType()
    {
        return $this->ticketType;
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
} 
