<?php
/**
 * User Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="evp_users")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\UserRepository")
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Order", mappedBy="user")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var Ticket
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Ticket", mappedBy="user")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $tickets;

    /**
     * Sets ArrayCollection
     */
    public function __construct() {
        $this->tickets = new ArrayCollection;
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
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * @param Order $orders
     * @return self
     */
    public function setOrder($orders)
    {
        $this->order = $orders;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Outputs User email
     *
     * @return string
     */
    public function __toString() {
        return (string)$this->email;
    }

    /**
     * @return static
     */
    public static function create() {
        return new self;
    }
}
