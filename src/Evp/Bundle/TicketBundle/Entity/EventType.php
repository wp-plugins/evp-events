<?php
/**
 * EventType Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;
use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;

/**
 * EventType
 *
 * @ORM\Table(name="evp_event_types")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\EventTypeRepository")
 * @GEDMO\TranslationEntity(class="Evp\Bundle\TicketBundle\Entity\Translation")
 */
class EventType
{
    const LABEL_LOCALE = 'admin.entity.locale.general_label';
    const LABEL_NAME = 'entity.event_type.name';
    const LABEL_MAX_TICKETS_PER_USER = 'entity.event_type.max_tickets_per_user';
    const LABEL_STATUS = 'admin.status.general_label';
    const LABEL_STEPS = 'admin.event_type.steps';
    const LABEL_INVOICING_ENABLED = 'entity.event_type.invoicing_enabled';
    const LABEL_PAY_BY_INVOICE_ENABLED = 'entity.event_type.pay_by_invoice_enabled';

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
     * @TicketAdmin\ListedColumn("entity.event_type.name")
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="max_tickets_per_user", type="integer", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event_type.max_tickets_per_user")
     */
    private $maxTicketsPerUser;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     * @TicketAdmin\ListedColumn("admin.status.general_label")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Event", mappedBy="eventType")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\EventTypeStep", mappedBy="eventType")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $formSteps;

    /**
     * @var
     * @ORM\Column(name="invoicing_enabled", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event_type.invoicing_enabled")
     */
    private $invoicingEnabled = false;

    /**
     * @var bool
     * @ORM\Column(name="pay_by_invoice_enabled", type="boolean", nullable=false)
     * @TicketAdmin\ListedColumn("entity.event_type.pay_by_invoice_enabled")
     */
    private $payByInvoice = false;

    /**
     * @var string
     * @GEDMO\Locale
     */
    private $locale;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema", mappedBy="eventType")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $eventFieldSchemas;

    private $eventTypeSteps;

    /**
     * @param mixed $eventTypeSteps
     */
    public function setEventTypeSteps($eventTypeSteps)
    {
        $this->eventTypeSteps = $eventTypeSteps;
    }

    /**
     * @param boolean $payByInvoice
     * @return self
     */
    public function setPayByInvoice($payByInvoice)
    {
        $this->payByInvoice = $payByInvoice;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPayByInvoice()
    {
        return $this->payByInvoice;
    }

    /**
     * @return mixed
     */
    public function getEventTypeSteps()
    {
        return $this->eventTypeSteps;
    }

    /**
     * Constructs array collections.
     */
    public function __construct() {
        $this->formSteps = new ArrayCollection();
        $this->fieldSchemas = new ArrayCollection();
    }

    /**
     * @param mixed $invoicingEnabled
     * @return self
     */
    public function setInvoicingEnabled($invoicingEnabled)
    {
        $this->invoicingEnabled = $invoicingEnabled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoicingEnabled()
    {
        return $this->invoicingEnabled;
    }

    /**
     * @param string
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
     * @param int $formSteps
     * @return self
     */
    public function setFormSteps($formSteps)
    {
        $this->formSteps = $formSteps;

        return $this;
    }

    /**
     * @return int
     */
    public function getFormSteps()
    {
        return $this->formSteps;
    }

    /**
     * @param int $event
     * @return self
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return int
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $maxTicketsPerUser
     * @return self
     */
    public function setMaxTicketsPerUser($maxTicketsPerUser)
    {
        $this->maxTicketsPerUser = $maxTicketsPerUser;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxTicketsPerUser()
    {
        return $this->maxTicketsPerUser;
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
     * @param EventTypeFieldSchema $fieldSchemas
     * @return static
     */
    public function setEventFieldSchemas($fieldSchemas)
    {
        $this->fieldSchemas = $fieldSchemas;

        return $this;
    }

    /**
     * @return EventTypeFieldSchema[]
     */
    public function getEventFieldSchemas()
    {
        return $this->eventFieldSchemas;
    }

    public function addEventTypeSteps($steps, $em, $entityObj)
    {
        $stepClass = 'Evp\Bundle\TicketBundle\Entity\Step';
        if (is_array($steps) && count($steps)) {
            foreach ($steps as $id) {
                $stepObj = $em->getRepository($stepClass)->find($id);
                $eventTypeStep = new EventTypeStep();
                $eventTypeStep->setSteps($stepObj);
                $eventTypeStep->setStepOrder(1);
                $eventTypeStep->setEventType($entityObj);
                $em->persist($eventTypeStep);
                $em->flush();
            }
        }
    }

    public function deleteEventTypeSteps($em)
    {
        foreach ($this->getFormSteps() as $eventTypeStep) {
            $eventTypeStepID = $eventTypeStep->getId();
            $eventTypeStepObj = $em->getRepository('Evp\Bundle\TicketBundle\Entity\EventTypeStep')->find($eventTypeStepID);
            $em->remove($eventTypeStepObj);
            $em->flush();
        }
    }
}
