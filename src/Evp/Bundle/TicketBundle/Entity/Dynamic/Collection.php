<?php
namespace Evp\Bundle\TicketBundle\Entity\Dynamic;

use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;
use Evp\Bundle\TicketBundle\Entity\Ticket;

/**
 * Class Collection
 * @package Evp\Bundle\TicketBundle\Entity\Dynamic
 * @author d.glezeris
 */
class Collection {

    /**
     * @var Entity[]
     */
    private $commonDetails;
    /**
     * @var Entity[]
     */
    private $globalDetails;

    /**
     * @var Ticket[]
     */
    private $tickets;

    function __construct()
    {
        $this->commonDetails = new ArrayCollection();
        $this->globalDetails = new ArrayCollection();
    }

    /**
     * @param EventTypeFieldSchema[] $schemas
     */
    public function addGlobal($schemas)
    {
        $this->globalDetails->add(new Entity($schemas));
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\Dynamic\Entity[] $globalDetails
     */
    public function setGlobalDetails($globalDetails)
    {
        $this->globalDetails = $globalDetails;
    }

    /**
     * @return \Evp\Bundle\TicketBundle\Entity\Dynamic\Entity[]
     */
    public function getGlobalDetails()
    {
        return $this->globalDetails;
    }

    /**
     * @param EventTypeFieldSchema[] $schemas
     */
    public function addCommon($schemas)
    {
        $this->commonDetails->add(new Entity($schemas));
    }

    /**
     * @param mixed $commonDetails
     */
    public function setCommonDetails($commonDetails)
    {
        $this->commonDetails = $commonDetails;
    }

    /**
     * @return mixed
     */
    public function getCommonDetails()
    {
        return $this->commonDetails;
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     * @return Collection
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
        return $this;
    }

    /**
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    public function getTickets()
    {
        return $this->tickets;
    }
}
