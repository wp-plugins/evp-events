<?php
/**
 * EventTypeStep Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Evp\Bundle\TicketAdminBundle\Annotation as TicketAdmin;

/**
 * EventTypeStep
 *
 * @ORM\Table(name="evp_event_type_steps",
 *      uniqueConstraints={@ORM\UniqueConstraint(
 *          name="unq_idx_evp_event_type_steps", columns={"form_step_id", "event_type_id", "step_order"}
 *      )}
 * )
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\EventTypeStepRepository")
 */
class EventTypeStep
{
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
     * @var int
     *
     * @ORM\Column(name="form_step_id", type="integer", length=8, nullable=false)
     */
    private $formStep;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\EventType", inversedBy="formSteps")
     * @ORM\JoinColumn(name="event_type_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $eventType;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Evp\Bundle\TicketBundle\Entity\Step", inversedBy="eventTypes")
     * @ORM\JoinColumn(name="form_step_id", referencedColumnName="id", onDelete="RESTRICT")
     * @TicketAdmin\ListedColumn("entity.event_type_step.step_name")
     */
    private $steps;

    /**
     * @var int
     *
     * @ORM\Column(name="step_order", type="integer", length=4, nullable=false)
     * @TicketAdmin\ListedColumn("entity.event_type_step.step_order")
     */
    private $stepOrder;

    /**
     * @param mixed $steps
     * @return self
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSteps()
    {
        return $this->steps;
    }


    /**
     * @param int $eventType
     * @return self
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * @return int
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param string $formStep
     * @return self
     */
    public function setFormStep($formStep)
    {
        $this->formStep = $formStep;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormStep()
    {
        return $this->formStep;
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
     * @param int $stepOrder
     * @return self
     */
    public function setStepOrder($stepOrder)
    {
        $this->stepOrder = $stepOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getStepOrder()
    {
        return $this->stepOrder;
    }
}
