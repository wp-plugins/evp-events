<?php
/**
 *Validates maximum tickets per user for EventType
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class MaxTicketsPerUserValdator
 */
class MaxTicketsPerUserValidator extends ConstraintValidator
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
        $maxTicketsPerUser = $this->entityManager
            ->getRepository('EvpTicketBundle:EventType')
            ->getEventTypeByEvent($constraint->getEvent())
            ->getMaxTicketsPerUser();

        $orderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(
                array(
                    'event' => $constraint->getEvent(),
                    'user' => $constraint->getUser(),
                )
            );
        $currentAmount = 0;
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $detail) {
                $currentAmount += intval($detail->getTicketsCount());
            }
        }

        if(intval($value) + intval($currentAmount) > intval($maxTicketsPerUser)) {
            $this->context->addViolation($constraint->message, array('%string%' => $maxTicketsPerUser), $maxTicketsPerUser);
        }
    }
}