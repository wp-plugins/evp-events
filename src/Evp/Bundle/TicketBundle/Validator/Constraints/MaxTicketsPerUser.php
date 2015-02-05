<?php
/**
 * MaxTicketsPerUser constraint
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class MaxTicketsPerUser
 * @Annotation
 */
class MaxTicketsPerUser extends Constraint
{
    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    public $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    public $user;

    /**
     * @var string
     */
    public $message = 'Tickets limit of %string% reached for this event';

    /**
     * Sets Event entity for proper validations
     * @param array $options
     */
    public function __construct($options) {
        $this->event = $options['event'];
        $this->user = $options['user'];
        $this->message = $options['message'];
    }

    /**
     * @return string
     */
    public function validatedBy() {
        return 'evp_max_tickets_per_user_validator';
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
     * @return \Evp\Bundle\TicketBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Gets Event Entity
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function getEvent() {
        return $this->event;
    }
}
