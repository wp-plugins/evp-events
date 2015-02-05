<?php

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Token
 *
 * @ORM\Table(name="evp_ticket_tokens", uniqueConstraints={@ORM\UniqueConstraint(name="token_idx_evp_ticket_tokens", columns={"token"})})
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\TicketTokenRepository")
 */
class TicketToken
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
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var boolean
     *
     * @ORM\Column(name="used", type="boolean")
     */
    private $used;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", onDelete="RESTRICT")
     */
    private $event;

    /**
     * @var Ticket
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Ticket")
     * @ORM\JoinColumn(name="ticket_id", onDelete="RESTRICT")
     */
    private $ticket;

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
     * @param Ticket $ticket
     *
     * @return TicketToken
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return TicketToken
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set used
     *
     * @param boolean $used
     * @return TicketToken
     */
    public function setUsed($used)
    {
        $this->used = $used;
    
        return $this;
    }

    /**
     * Get used
     *
     * @return boolean 
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set event
     *
     * @param Event $event
     * @return TicketToken
     */
    public function setEvent($event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
