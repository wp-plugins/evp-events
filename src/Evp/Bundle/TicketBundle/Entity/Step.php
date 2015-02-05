<?php
/**
 * Step Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Step
 *
 * @ORM\Table(name="evp_steps")
 * @ORM\Entity(repositoryClass="Evp\Bundle\TicketBundle\Repository\StepRepository")
 */
class Step
{
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
     * @ORM\Column(name="parameter", type="string", length=255, nullable=false)
     */
    private $parameter;

    /**
     * @var int
     *
     * @ORM\OneToMany(targetEntity="Evp\Bundle\TicketBundle\Entity\EventTypeStep", mappedBy="steps")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $eventTypes;

    /**
     * Gets the $parameter as class string
     *
     * @return string
     */
    public function __toString() {
        return $this->parameter;
    }

    /**
     * @param int $eventTypes
     * @return self
     */
    public function setEventTypes($eventTypes)
    {
        $this->eventTypes = $eventTypes;

        return $this;
    }

    /**
     * @return int
     */
    public function getEventTypes()
    {
        return $this->eventTypes;
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
     * @param string $parameter
     * @return self
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * @return string
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}
