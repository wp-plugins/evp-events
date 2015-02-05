<?php

namespace Evp\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketBundle\Entity\Order;

/**
 * CallbackRecord
 *
 * @ORM\Table(name="evp_payment_callback")
 * @ORM\Entity
 */
class PaymentCallback
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="handlerName", type="string", length=255)
     */
    private $handlerName;

    /**
     * @var array
     *
     * @ORM\Column(name="parsedRequestData", type="json_array")
     */
    private $parsedRequestData;

    /**
     * @var array
     *
     * @ORM\Column(name="rawRequestData", type="json_array")
     */
    private $rawRequestData;

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="text", length=255, nullable=true)
     */
    private $errorMessage;

    /**
     * @var Order
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $order;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set handlerName
     *
     * @param string $handlerName
     * @return self
     */
    public function setHandlerName($handlerName)
    {
        $this->handlerName = $handlerName;
    
        return $this;
    }

    /**
     * Get handlerName
     *
     * @return string 
     */
    public function getHandlerName()
    {
        return $this->handlerName;
    }

    /**
     * Set parsedRequestData
     *
     * @param array $parsedRequestData
     * @return self
     */
    public function setParsedRequestData($parsedRequestData)
    {
        $this->parsedRequestData = $parsedRequestData;
    
        return $this;
    }

    /**
     * Get parsedRequestData
     *
     * @return array 
     */
    public function getParsedRequestData()
    {
        return $this->parsedRequestData;
    }

    /**
     * @param Order $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

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
     * @param array $rawRequestData
     * @return self
     */
    public function setRawRequestData($rawRequestData)
    {
        $this->rawRequestData = $rawRequestData;

        return $this;
    }

    /**
     * @return array
     */
    public function getRawRequestData()
    {
        return $this->rawRequestData;
    }

    /**
     * @param string $error
     * @return self
     */
    public function setErrorMessage($error)
    {
        $this->errorMessage = $error;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return self
     */
    public static function create() {
        return new self;
    }

}
