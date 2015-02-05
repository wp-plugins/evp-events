<?php
/**
 * Order Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Evp\Bundle\TicketBundle\Entity\Step\InvoiceDetails;
use Evp\Bundle\TicketMaintenanceBundle\Entity\TokenAwareInterface;
use JMS\Serializer\Annotation as JMS;
use Evp\Bundle\TicketMaintenanceBundle\Annotation as Maintenance;

/**
 * Order
 *
 * @ORM\Table(name="evp_orders", uniqueConstraints={@ORM\UniqueConstraint(name="token_idx", columns={"token"})})
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\OrderRepository")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class Order implements TokenAwareInterface, UtcOffsetMutableInterface
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DONE = 'done';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @TicketAdmin\ListedColumn("admin.index.entity.id")
     * @TicketAdmin\FilteredColumn
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\User", inversedBy="order", cascade="persist")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("admin.index.entity.user")
     * @TicketAdmin\FilteredColumn("email")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("admin.index.entity.date_created")
     * @TicketAdmin\FilteredColumn
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="date_finished", type="datetime", nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.date_finished")
     * @TicketAdmin\FilteredColumn
     */
    private $dateFinished;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     * @JMS\Expose
     * @TicketAdmin\ListedColumn("admin.index.entity.status")
     * @TicketAdmin\FilteredColumn
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="date_expires", type="datetime", nullable=false)
     */
    private $stateExpires;

    /**
     * @var int
     *
     * @ORM\Column(name="tickets_count", type="integer", nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.tickets_count")
     * @TicketAdmin\FilteredColumn
     */
    private $ticketsCount;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", scale=4, precision=10, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.order_price")
     * @TicketAdmin\FilteredColumn
     */
    private $orderPrice;

    /**
     * @var string
     * @ORM\Column(name="payment_type", type="string", length=32, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.payment_type")
     * @TicketAdmin\FilteredColumn
     */
    private $paymentType;

    /**
     * @var bool
     * @ORM\Column(name="invoice_required", type="boolean", nullable=true)
     */
    private $invoiceRequired = false;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Step\InvoiceDetails", mappedBy="order")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=true)
     * @Maintenance\UniqueToken
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="test_mode", type="boolean", nullable=true)
     */
    private $testMode = false;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_amount", type="decimal", scale=4, precision=10, nullable=true)
     */
    private $discountAmount;

    /**
     * @return mixed
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param mixed $discountAmount
     *
     * @return Order
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    /**
     * @param mixed $testMode
     * @return self
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;

        return $this;
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param int $invoice
     * @return self
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return InvoiceDetails
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param string $paymentType
     * @return self
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @param string $invoiceRequired
     * @return self
     */
    public function setInvoiceRequired($invoiceRequired)
    {
        $this->invoiceRequired = $invoiceRequired;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceRequired()
    {
        return $this->invoiceRequired;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param Event $event
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
     * @param string $dateFinished
     * @return self
     */
    public function setDateFinished($dateFinished)
    {
        $this->dateFinished = $dateFinished;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFinished()
    {
        return $this->dateFinished;
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
     * @param float $orderPrice
     * @return self
     */
    public function setOrderPrice($orderPrice)
    {
        $this->orderPrice = $orderPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getOrderPrice()
    {
        return $this->orderPrice;
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
     * @param User $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $stateExpires
     * @return self
     */
    public function setStateExpires($stateExpires)
    {
        $this->stateExpires = $stateExpires;

        return $this;
    }

    /**
     * @return string
     */
    public function getStateExpires()
    {
        return $this->stateExpires;
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
     * @return static
     */
    public static function create() {
        return new self;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCurrentDateCreated() {
        if ($this->dateCreated === null) {
            $this->dateCreated = new \DateTime('now');
        }
    }
}
