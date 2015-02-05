<?php
/**
 * AvailableToBuyCount constraint
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class AvailableToBuyCount
 * @Annotation
 */
class AvailableToBuyCount extends Constraint
{
    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    public $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\TicketType
     */
    public $ticketType;

    /**
     * @var string
     */
    public $message = 'For this ticket type only %string% tickets left';

    /**
     * Sets Event entity for proper validations
     * @param array $options
     */
    public function __construct($options) {
        $this->event = $options['event'];
        $this->ticketType = $options['ticketType'];
        $this->message = $options['message'];
    }

    /**
     * @return string
     */
    public function validatedBy() {
        return 'evp_available_to_buy_count_validator';
    }

    /**
     * Constraint targets
     * @return string
     */
    public function getTargets() {
        return self::PROPERTY_CONSTRAINT;
    }

    /**
     * Gets User Entity
     * @return \Evp\Bundle\TicketBundle\Entity\TicketType
     */
    public function getTicketType() {
        return $this->ticketType;
    }

    /**
     * Gets Event Entity
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function getEvent() {
        return $this->event;
    }
}
