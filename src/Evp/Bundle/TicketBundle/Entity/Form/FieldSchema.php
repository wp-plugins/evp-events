<?php

namespace Evp\Bundle\TicketBundle\Entity\Form;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;

/**
 * FieldSchema
 *
 * @ORM\Table(name="evp_field_schemas")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\FieldSchemaRepository")
 * @GEDMO\TranslationEntity(class="Evp\Bundle\TicketBundle\Entity\Translation")
 * @ORM\HasLifecycleCallbacks
 */
class FieldSchema
{
    const LABEL_NAME = 'entity.field_schema.name';
    const LABEL_TYPE = 'entity.field_schema.type';
    const LABEL_VALIDATOR = 'entity.field_schema.validator';
    const LABEL_FIELD_ORDER = 'entity.field_schema.field_order';
    const LABEL_LABEL = 'entity.field_schema.label';
    const LABEL_LOCALE = 'admin.entity.locale.general_label';

    const LABEL_SCHEMA_TYPE = 'admin.field_schema.schema_type';
    const LABEL_SCHEMA_GENERAL = 'admin.field_schema.schema_general';
    const LABEL_SCHEMA_COMMON = 'admin.field_schema.schema_common';

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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, unique=true)
     * @TicketAdmin\ListedColumn("entity.field_schema.name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     * @TicketAdmin\ListedColumn("entity.field_schema.type")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="validator", type="string", length=255, nullable=true)
     * @TicketAdmin\ListedColumn("entity.field_schema.validator")
     */
    private $validator;

    /**
     * @var int
     *
     * @ORM\Column(name="field_order", type="integer", length=4, nullable=true)
     * @TicketAdmin\ListedColumn("entity.field_schema.field_order")
     */
    private $fieldOrder;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     * @TicketAdmin\ListedColumn("entity.field_schema.label")
     */
    private $label;

    /**
     * @var string
     * @GEDMO\Locale
     */
    private $locale;

    /**
     * @param string $locale
     * @return static
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $label
     * @return static
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param int $fieldOrder
     * @return static
     */
    public function setFieldOrder($fieldOrder)
    {
        $this->fieldOrder = $fieldOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getFieldOrder()
    {
        return $this->fieldOrder;
    }

    /**
     * @param string $validator
     * @return static
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidator()
    {
        return $this->validator;
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
     * Set name
     *
     * @param string $name
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return static
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @ORM\PreFlush
     */
    public function preFlush() {
        $this->validator = preg_replace('/\\\\+/', '\\', $this->validator);
        $this->name = preg_replace("/[^A-Za-z0-9]/", '', $this->name);
    }

    /**
     * @ORM\PostLoad
     */
    public function onLoad() {
        $this->label = stripslashes($this->label);
    }

    /**
     * @return static
     */
    public static function create() {
        return new static();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
