<?php
/**
 * EventTypeStep Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\EventType;
use Evp\Bundle\TicketBundle\Entity\EventTypeStep;
use Evp\Bundle\TicketBundle\Entity\Step;

/**
 * Class EventTypeStep
 */
class EventTypeStepRepository extends EntityRepository {

    /**
     * Returns EventTypeStep by Step
     *
     * @param string                                    $name
     * @param \Evp\Bundle\TicketBundle\Entity\EventType $et
     *
     * @return \Evp\Bundle\TicketBundle\Entity\EventTypeStep
     */
    public function getEventTypeStepByStep($name, EventType $et) {
        return $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->join('ets.steps', 'st')
            ->where('st.parameter = :nm')
            ->andWhere('ets.eventType = :et')
            ->orderBy('ets.stepOrder', 'asc')
            ->setMaxResults(1)
            ->setParameters(
                array(
                    'nm' => $name,
                    'et' => $et,
                )
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns all EventTypeSteps by EventType
     *
     * @param \Evp\Bundle\TicketBundle\Entity\EventType $et
     * @return \Evp\Bundle\TicketBundle\Entity\EventTypeStep[]
     */
    public function getEventTypeStepsForEventType(EventType $et) {
        return $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->where('ets.eventType = :et')
            ->orderBy('ets.stepOrder', 'asc')
            ->setParameters(
                array(
                    'et' => $et,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns next EventTypeStep
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param  string                               $name
     *
     * @return \Evp\Bundle\TicketBundle\Entity\EventTypeStep
     */
    public function getNextStepForEventAndCurrentStepName(Event $ev, $name)
    {
        $eventTypeStep = $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->join('ets.steps', 'st')
            ->where('ets.eventType = :et')
            ->andWhere('st.parameter = :nm')
            ->setParameters(array(
                    'et' => $ev->getEventType(),
                    'nm' => $name,
                ))
            ->getQuery()
            ->getOneOrNullResult();

        return $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->where('ets.eventType = :et')
            ->andWhere('ets.stepOrder > :or')
            ->orderBy('ets.stepOrder', 'asc')
            ->setMaxResults(1)
            ->setParameters(
                array(
                    'et' => $ev->getEventType(),
                    'or' => $eventTypeStep->getStepOrder(),
                )
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns previous EventTypeStep
     *
     * @param Event  $ev
     * @param string $name
     *
     * @return \Evp\Bundle\TicketBundle\Entity\EventTypeStep
     */
    public function getPreviousStepForEventAndCurrentStepName(Event $ev, $name)
    {
        $eventTypeStep = $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->join('ets.steps', 'st')
            ->where('ets.eventType = :et')
            ->andWhere('st.parameter = :nm')
            ->setParameters(array(
                    'et' => $ev->getEventType(),
                    'nm' => $name,
                ))
            ->getQuery()
            ->getOneOrNullResult();

        return $this->_em->createQueryBuilder()
            ->select('ets')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->where('ets.eventType = :et')
            ->andWhere('ets.stepOrder < :or')
            ->orderBy('ets.stepOrder', 'desc')
            ->setMaxResults(1)
            ->setParameters(
                array(
                    'et' => $ev->getEventType(),
                    'or' => $eventTypeStep->getStepOrder(),
                )
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $eventType
     *
     * @return array
     */
    public function getEventTypeSteps($eventType)
    {
        $q = $this->_em->createQueryBuilder()
            ->select('DISTINCT IDENTITY (ets.steps)')
            ->from('Evp\Bundle\TicketBundle\Entity\EventTypeStep', 'ets')
            ->join('ets.eventType', 'et')
            ->where('et.id = :id')
            ->setParameter('id', $eventType)
            ->getQuery();

        $data = $q->getArrayResult();
        $ids = array();
        foreach($data as $d) {
            $ids[] = $d[1];
        }

        return $ids;
    }
}
