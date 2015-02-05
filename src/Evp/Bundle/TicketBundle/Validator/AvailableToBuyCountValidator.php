<?php
/**
 *Validates available to buy tickets count
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AvailableToBuyCountValdator
 */
class AvailableToBuyCountValidator extends ConstraintValidator
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint) {
        $availableTicketsCnt = $this->entityManager
            ->getRepository('EvpTicketBundle:TicketType')
            ->getAvailableCountByEventAndTicketType($constraint->getEvent(), $constraint->getTicketType());

        if ($constraint->getTicketType()->getTicketsCount() !== null) {
            if(intval($value) > intval($availableTicketsCnt)) {
                $this->context->addViolation($constraint->message, array('%string%' => $availableTicketsCnt), $availableTicketsCnt);
            }
        }
    }
}