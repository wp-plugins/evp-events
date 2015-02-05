<?php
/**
 * Step Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Step;

/**
 * Class StepRepository
 */
class StepRepository extends EntityRepository {

    /**
     * Add required methods
     */

    public function findAllSteps()
    {
        $steps = array();
        $stepsArray = $this->_em->createQueryBuilder()
            ->select('s.id, s.parameter')
            ->from('Evp\Bundle\TicketBundle\Entity\Step', 's')
            ->getQuery()
            ->getArrayResult();

        foreach($stepsArray as $s) {
            $steps[$s['id']] = $s['parameter'];
        }

        return $steps;
    }

    /**
     * Gets the Step names for given Event in correct order
     *
     * @param Event $e
     * @return array
     */
    public function getStepNamesForEvent(Event $e)
    {
        $ets = $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->join('ets.steps', 's')
            ->where('ets.eventType = :e')
            ->orderBy('ets.stepOrder', 'asc')
            ->setParameters(
                array(
                    'e' => $e->getEventType(),
                )
            )
            ->getQuery()
            ->getResult();

        $stepNames = array();
        foreach ($ets as $et) {
            $stepNames[] = $et->getSteps()->getParameter();
        }
        return $stepNames;
    }
}
