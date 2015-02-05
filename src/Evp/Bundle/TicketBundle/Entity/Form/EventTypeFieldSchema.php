<?php

namespace Evp\Bundle\TicketBundle\Entity\Form;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Evp\Bundle\TicketBundle\Entity\EventType;

/**
 * EventTypeFieldSchema
 *
 * @ORM\Table(
 *      name="evp_event_type_field_schemas",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unq_idx_evp_event_type_field_schemas", columns={"event_type_id", "field_schema_id"}
 *          )
 *      }
 * )
 * @ORM\Entity
 */
class EventTypeFieldSchema
{
    const LABEL_FIELD_SCHEMA = 'entity.event_type_field_schema.field_schema';
    const LABEL_REQUIRED = 'entity.event_type_field_schema.required';
    const LABEL_REQUIRED_FOR_ALL = 'entity.event_type_field_schema.required_for_all';

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
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Form\FieldSchema")
     * @ORM\JoinColumn(name="field_schema_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("entity.event_type_field_schema.field_schema")
     */
    private $fieldSchema;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\EventType", inversedBy="eventFieldSchemas")
     * @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $eventType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_required", type="boolean")
     * @TicketAdmin\ListedColumn("entity.event_type_field_schema.required")
     */
    private $isRequired;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_required_for_all", type="boolean")
     * @TicketAdmin\ListedColumn("entity.event_type_field_schema.required_for_all")
     */
    private $isRequiredForAll;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_made_by_admin", type="boolean")
     */
    private $isMadeByAdmin;

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
     * Set fieldSchema
     *
     * @param object $fieldSchema
     * @return EventTypeFieldSchema
     */
    public function setFieldSchema($fieldSchema)
    {
        $this->fieldSchema = $fieldSchema;
    
        return $this;
    }

    /**
     * Get fieldSchema
     *
     * @return FieldSchema
     */
    public function getFieldSchema()
    {
        return $this->fieldSchema;
    }

    /**
     * Set eventType
     *
     * @param EventType $eventType
     * @return EventTypeFieldSchema
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    
        return $this;
    }

    /**
     * Get eventType
     *
     * @return \stdClass 
     */
    public function getEventType()
    {
        return $this->eventType;
    }


    /**
     * Set isRequired
     *
     * @param boolean $isRequired
     * @return static
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get isRequired
     *
     * @return boolean
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set isMadeByAdmin
     *
     * @param boolean $isMadeByAdmin
     * @return static
     */
    public function setIsMadeByAdmin($isMadeByAdmin)
    {
        $this->isMadeByAdmin = $isMadeByAdmin;

        return $this;
    }

    /**
     * Get isMadeByAdmin
     *
     * @return boolean
     */
    public function getIsMadeByAdmin()
    {
        return $this->isMadeByAdmin;
    }

    /**
     * Get isRequiredForAll
     *
     * @param boolean $isRequiredForAll
     * @return static
     */
    public function setIsRequiredForAll($isRequiredForAll)
    {
        $this->isRequiredForAll = $isRequiredForAll;
        return $this;
    }

    /**
     * Set isRequireForAll
     *
     * @return boolean
     */
    public function getIsRequiredForAll()
    {
        return $this->isRequiredForAll;
    }

    /**
     * @return static
     */
    public static function create() {
        return new static();
    }

}
