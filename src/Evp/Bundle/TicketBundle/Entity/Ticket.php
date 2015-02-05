<?php
/**
 * Ticket Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use JMS\Serializer\Annotation as JMS;
use Evp\Bundle\TicketMaintenanceBundle\Annotation as Maintenance;

/**
 * Ticket
 *
 * @ORM\Table(name="evp_tickets", uniqueConstraints={@ORM\UniqueConstraint(name="token_idx_evp_tickets", columns={"token"})})
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\TicketRepository")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class Ticket implements UtcOffsetMutableInterface
{
    const STATUS_IDLE = 'idle';
    const STATUS_UNUSED = 'unused';
    const STATUS_USED = 'used';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Event", inversedBy="tickets")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\TicketType", inversedBy="tickets")
     * @ORM\JoinColumn(name="ticket_type_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $ticketType;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", scale=4, precision=10, nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     */
    private $status = self::STATUS_IDLE;

    /**
     * @var string
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     */
    private $dateModified;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=true)
     * @JMS\Expose
     * @Maintenance\UniqueToken
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\User", inversedBy="tickets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Step\OrderDetails")
     * @ORM\JoinColumn(name="order_details_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $orderDetails;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Discount")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $discount;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer")
     * @ORM\JoinColumn(name="ticket_examiner_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $ticketExaminer;

    /**
     * @var string
     *
     * @ORM\Column(name="date_used", type="datetime", nullable=true)
     */
    private $dateUsed;

    /**
     * @var \stdClass
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Seat\Matrix")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $seat;

    /**
     * Constructs array collections.
     */
    public function __construct() {
        $this->order = new ArrayCollection();
    }

    /**
     * @param string $seat
     * @return self
     */
    public function setSeat($seat)
    {
        $this->seat = $seat;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeat()
    {
        return $this->seat;
    }

    /**
     * @param Discount $discount
     * @return self
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return Discount
     */
    public function getDiscount()
    {
        return $this->discount;
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
     * @return OrderDetails
     */
    public function getOrderDetails()
    {
        return $this->orderDetails;
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
     * @return string
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param string $dateModified
     * @return self
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param Event $event
     * @return self
     */
    public function setEvent(Event $event)
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
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        if ($status === self::STATUS_USED) {
            $this->setDateUsed(new \DateTime('now'));
        }

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
     * @param TicketType $ticketType
     * @return self
     */
    public function setTicketType(TicketType $ticketType)
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
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $ticketExaminer
     */
    public function setTicketExaminer($ticketExaminer)
    {
        $this->ticketExaminer = $ticketExaminer;
    }

    /**
     * @return TicketExaminer
     */
    public function getTicketExaminer()
    {
        return $this->ticketExaminer;
    }

    /**
     * @param string $dateUsed
     */
    public function setDateUsed($dateUsed)
    {
        $this->dateUsed = $dateUsed;
    }

    /**
     * @return string
     */
    public function getDateUsed()
    {
        return $this->dateUsed;
    }

    /**
     * @return static
     */
    public static function create() {
        return new self;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        if (empty($this->dateCreated)) {
            $this->dateCreated = new \DateTime();
        }
    }

    /**
     * @ORM\PreFlush
     */
    public function preFlush() {
        $this->dateModified = new \DateTime;
    }

    /**
     * @return string
     */
    public function __toString() {
        return '#' .$this->id;
    }
}
