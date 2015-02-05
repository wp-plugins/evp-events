<?php
/**
 * InvoiceDetails Entity for Invoice information
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */


namespace Evp\Bundle\TicketBundle\Entity\Step;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class InvoiceDetails
 *
 * @ORM\Table(name="evp_invoice_details")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\InvoiceDetailsRepository")
 */
class InvoiceDetails {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="vat_code", type="string", length=255, nullable=true)
     */
    private $vatCode;

    /**
     * @var int
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Order", inversedBy="invoice")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    private $number;


    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return InvoiceDetails
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @param string $address
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return stripslashes($this->address);
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
     * @param int $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $vatCode
     * @return self
     */
    public function setVatCode($vatCode)
    {
        $this->vatCode = $vatCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatCode()
    {
        return $this->vatCode;
    }
} 
