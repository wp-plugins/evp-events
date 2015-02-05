<?php

namespace Evp\Bundle\TicketBundle\Entity\Form;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Entity\User;

/**
 * FieldRecord
 *
 * @ORM\Table(name="evp_field_records",
 *      uniqueConstraints={
 *              @ORM\UniqueConstraint(
 *                  name="unq_idx_evp_field_records", columns={"event_id", "field_schema_id", "ticket_id"}
 *              )
 *      }
 * )
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\FieldRecordRepository")
 */
class FieldRecord
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @TicketAdmin\ListedColumn("admin.index.entity.id")
     */
    private $id;

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
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Form\FieldSchema")
     * @ORM\JoinColumn(name="field_schema_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("entity.field_schema.name")
     */
    private $fieldSchema;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     * @TicketAdmin\ListedColumn("admin.index.entity.field_schema_value")
     */
    private $value;

    /**
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Ticket")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("admin.index.entity.ticket")
     */
    private $ticket;

    /**
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("admin.index.entity.user")
     */
    private $user;

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
     * @return \stdClass
     */
    public function getUser()
    {
        return $this->user;
    }

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
     * Set event
     *
     * @param Event $event
     * @return self
     */
    public function setEvent($event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get eventType
     *
     * @return \stdClass 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set fieldSchema
     *
     * @param  FieldSchema $fieldSchema
     * @return self
     */
    public function setFieldSchema($fieldSchema)
    {
        $this->fieldSchema = $fieldSchema;
    
        return $this;
    }

    /**
     * Get fieldSchema
     *
     * @return \stdClass 
     */
    public function getFieldSchema()
    {
        return $this->fieldSchema;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set ticket
     *
     * @param Ticket $ticket
     * @return self
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    
        return $this;
    }

    /**
     * Get ticket
     *
     * @return \stdClass 
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return static
     */
    public static function create() {
        return new static();
    }
}
