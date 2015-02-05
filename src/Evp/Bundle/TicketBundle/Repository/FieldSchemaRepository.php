<?php
/**
 * FieldSchema Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\EventType;
use Evp\Bundle\TicketBundle\Entity\User;

/**
 * Class FieldSchemaRepository
 */
class FieldSchemaRepository extends EntityRepository {

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     *
     * @return array
     */
    public function getUnfilledFieldsByEventAndUser(Event $ev, User $us) {
        $requiredSchemas = $this->_em->createQueryBuilder()
            ->select('fs.id')
            ->from('Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema', 'etfs')
            ->join('etfs.fieldSchema', 'fs')
            ->where('etfs.isRequiredForAll = 0')
            ->andWhere('etfs.isRequired = 1')
            ->andWhere('etfs.eventType = :et')
            ->setParameters(
                array(
                    'et' => $ev->getEventType(),
                )
            )
            ->getQuery()
            ->getArrayResult();
        $requiredSchemas = array_map(function ($el) { return $el['id']; }, $requiredSchemas);

        $result = null;
        if (!empty($requiredSchemas))
        {
            $filledSchemas = $this->_em->createQueryBuilder()
                ->select('fs.id')
                ->from('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord', 'fr')
                ->join('fr.fieldSchema', 'fs')
                ->where('fr.event = :ev')
                ->andWhere('fr.user = :us')
                ->andWhere('fr.fieldSchema IN (:in)')
                ->setParameters(
                    array(
                        'ev' => $ev,
                        'us' => $us,
                        'in' => $requiredSchemas
                    )
                )
                ->getQuery()
                ->getArrayResult();
            $filledSchemas = array_map(function ($el) { return $el['id']; }, $filledSchemas);

            if (empty($filledSchemas)) {
                $result = $this->_em->createQueryBuilder()
                    ->select('etfs')
                    ->from('Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema', 'etfs')
                    ->join('etfs.fieldSchema', 'fs')
                    ->andWhere('etfs.isRequiredForAll = 0')
                    ->andWhere('etfs.eventType = :et')
                    ->setParameters(
                        array(
                            'et' => $ev->getEventType(),
                        )
                    )
                    ->getQuery()
                    ->getResult();
            } else {
                $result = $this->_em->createQueryBuilder()
                    ->select('etfs')
                    ->from('Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema', 'etfs')
                    ->join('etfs.fieldSchema', 'fs')
                    ->andWhere('etfs.isRequiredForAll = 0')
                    ->andWhere('etfs.isRequired = 1')
                    ->andWhere('etfs.eventType = :et')
                    ->andWhere('fs.id NOT IN (:not)')
                    ->setParameters(
                        array(
                            'et' => $ev->getEventType(),
                            'not' => $filledSchemas,
                        )
                    )
                    ->getQuery()
                    ->getResult();
            }
        }
        return $result;
    }

    /**
     * Gets all fields per EventType based on isRequiredForAll parameter
     *
     * @param \Evp\Bundle\TicketBundle\Entity\EventType $et
     * @param int $forAll
     * @return \Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema[]
     */
    public function getByEventTypeAllOrdered(EventType $et, $forAll) {
        return $this->_em->createQueryBuilder()
            ->select('etfs')
            ->from('Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema', 'etfs')
            ->join('etfs.fieldSchema', 'fs')
            ->where('etfs.eventType = :et')
            ->andWhere('etfs.isRequiredForAll = :is')
            ->orderBy('fs.fieldOrder', 'asc')
            ->setParameters(
                array(
                    'et' => $et,
                    'is' => $forAll,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets the all notRequiredForAll FieldSchemas
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param bool $forAll
     * @return \Evp\Bundle\TicketBundle\Entity\Form\FieldSchema[]
     */
    public function getByEventAndNotRequiredForAll(Event $ev, $forAll = false) {
        $result = $this->getByEventTypeAllOrdered($ev->getEventType(), $forAll);

        $schemas = array();
        foreach ($result as $map) {
            $schemas[] = $map->getFieldSchema();
        }
        return $schemas;
    }
}
