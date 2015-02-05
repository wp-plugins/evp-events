<?php
/**
 * Event Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketMaintenanceBundle\Entity\TokenAwareInterface;
use Gedmo\Mapping\Annotation as GEDMO;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Evp\Bundle\TicketMaintenanceBundle\Annotation as Maintenance;

/**
 * Event
 *
 * @ORM\Table(name="evp_events")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 * @GEDMO\TranslationEntity(class="Evp\Bundle\TicketBundle\Entity\Translation")
 */
class Event implements TokenAwareInterface
{
    const LABEL_EVENT_TYPE = 'entity.event.event_type';
    const LABEL_NAME = 'entity.event.name';
    const LABEL_DESCRIPTION = 'entity.event.description';
    const LABEL_CURRENCY = 'entity.event.currency';
    const LABEL_DEFAULT_LOCALE = 'entity.event.default_locale';
    const LABEL_DATE_STARTS = 'entity.event.date_starts';
    const LABEL_DATE_ENDS = 'entity.event.date_ends';
    const LABEL_DATE_ON_SALE = 'entity.event.date_on_sale';
    const LABEL_LOCALE = 'admin.entity.locale.general_label';
    const LABEL_STATUS = 'admin.status.general_label';
    const LABEL_GLOBAL_TEMPLATE_ENTITY = 'entity.event.template.uses_global_entity';
    const LABEL_SEAT_AREA = 'entity.event.seat_area';
    const LABEL_BREADCRUMBS_ENABLED = 'entity.event.template.breadcrumbs_enabled';
    const LABEL_COUNTRY_CODE = 'entity.event.country_code';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @TicketAdmin\ListedColumn("admin.index.entity.id")
     */
    private $id;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.name")
     */
    private $name;

    /**
     * @var string
     *
     * @GEDMO\Translatable
     * @ORM\Column(name="description", type="text", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.description")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3, nullable=false)
     */
    private $currency;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @ORM\Column(name="date_starts", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.date_starts")
     */
    private $dateStarts;

    /**
     * @var string
     *
     * @ORM\Column(name="date_ends", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.date_ends")
     */
    private $dateEnds;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\EventType", inversedBy="event")
     * @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $eventType;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Ticket", mappedBy="event")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $tickets;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\TicketType", mappedBy="event")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $ticketTypes;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("admin.status.general_label")
     */
    private $enabled = true;

    /**
     * @var string
     * @GEDMO\Locale
     */
    private $locale;

    /**
     * @var
     * @ORM\Column(name="default_locale", type="string", length=3, nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.default_locale")
     */
    private $defaultLocale = 'en';

    /**
     * @var string
     *
     * @ORM\Column(name="date_on_sale", type="datetime", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.date_on_sale")
     */
    private $dateOnSale = null;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", nullable=false)
     */
    private $countryCode = 'lt';

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=true)
     * @Maintenance\UniqueToken
     */
    private $token;

    /**
     * @var boolean
     * @ORM\Column(name="global_entity_template", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.template.uses_global_entity")
     */
    private $globalEntityTemplate;

    /**
     * @var \stdClass
     *
     * @ORM\OneToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Seat\Area")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $area;

    /**
     * @var bool
     * @ORM\Column(name="breadcrumbs_enabled", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event.template.breadcrumbs_enabled")
     */
    private $breadcrumbsEnabled = true;

    /**
     * Constructs array collections.
     */
    public function __construct() {
        $this->tickets = new ArrayCollection();
        $this->ticketTypes = new ArrayCollection();

        $this->dateEnds = new \DateTime();
        $this->dateStarts = new \DateTime();
        $this->dateCreated = new \DateTime();
        $this->dateOnSale = new \DateTime();
    }

    /**
     * @param \stdClass $area
     * @return self
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @param boolean $breadcrumbsEnabled
     * @return self
     */
    public function setBreadcrumbsEnabled($breadcrumbsEnabled)
    {
        $this->breadcrumbsEnabled = $breadcrumbsEnabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getBreadcrumbsEnabled()
    {
        return $this->breadcrumbsEnabled;
    }

    /**
     * @return \stdClass
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param boolean $globalEntityTemplate
     * @return self
     */
    public function setGlobalEntityTemplate($globalEntityTemplate)
    {
        $this->globalEntityTemplate = $globalEntityTemplate;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getGlobalEntityTemplate()
    {
        return $this->globalEntityTemplate;
    }

    /**
     * @param mixed $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $dateOnSale
     * @return self
     */
    public function setDateOnSale($dateOnSale)
    {
        $this->dateOnSale = $dateOnSale;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateOnSale()
    {
        return $this->dateOnSale;
    }

    /**
     * @param mixed $defaultLocale
     * @return self
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param int $ticketTypes
     * @return self
     */
    public function setTicketTypes($ticketTypes)
    {
        $this->ticketTypes = $ticketTypes;

        return $this;
    }

    /**
     * @return TicketType[]
     */
    public function getTicketTypes()
    {
        return $this->ticketTypes;
    }

    /**
     * @param boolean $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @var int
     *
     * @param $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @var int
     *
     * @param $tickets
     * @return self
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * @return int
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @var int
     *
     * @param $eventType
     * @return self
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * @return EventType
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param $dateCreated
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
     * @param $dateEnds
     * @return self
     */
    public function setDateEnds($dateEnds)
    {
        $this->dateEnds = $dateEnds;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateEnds()
    {
        return $this->dateEnds;
    }


    /**
     * @param $dateStarts
     * @return self
     */
    public function setDateStarts($dateStarts)
    {
        $this->dateStarts = $dateStarts;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateStarts()
    {
        return $this->dateStarts;
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
     * @return Event
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
        return stripslashes($this->name);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return stripslashes($this->description);
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Event
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @ORM\PrePersist
     *
     */

    public function prePersist()
    {
        $this->dateCreated = new \DateTime;
        $this->enabled = true;
    }

    /**
     * @ORM\PrePersist
     *
     */
    public function checkDateOnSale() {
        if ($this->dateOnSale === null) {
            $this->dateOnSale = $this->dateCreated;
        }
    }

    /**
     * @Assert\True(message = "message.error.date_starts_greater_date_ends")
     */
    public function isDateStartsLessDateEnds() {
        if ($this->dateStarts > $this->dateEnds) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Assert\True(message = "message.error.date_on_sale_greater_date_ends")
     */
    public function isDateOnSaleLessDateEnds() {
        if ($this->dateOnSale > $this->dateEnds) {
            return false;
        } else {
            return true;
        }
    }
}
