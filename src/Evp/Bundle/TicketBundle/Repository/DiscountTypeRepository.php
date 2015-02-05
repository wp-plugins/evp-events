<?php
/**
 * DiscountTypeRepository Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Discount;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;

/**
 * Class DiscountTypeRepository
 */
class DiscountTypeRepository extends EntityRepository {
    /**
     * Gets all active DiscountTypes by Event and Scope
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param string $sc
     * @return \Evp\Bundle\TicketBundle\Entity\DiscountType[]
     */
    public function getAllActiveByEventAndScope(Event $ev, $sc) {
        return $this->createQueryBuilder('dt')
            ->join('dt.ticketTypes', 'tt')
            ->join('tt.event', 'ev')
            ->andWhere('dt.status = :on')
            ->andWhere('dt.dateStarts <= :now')
            ->andWhere('dt.dateEnds >= :now')
            ->andWhere('dt.scope = :sc')
            ->andWhere('ev.id = :evid')
            ->setParameters(
                array(
                    'on' => 1,
                    'now' => new \DateTime,
                    'sc' => $sc,
                    'evid' => $ev->getId(),
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all active DiscountTypes by TicketType and Scope
     *
     * @param \Evp\Bundle\TicketBundle\Entity\TicketType $tt
     * @param string $sc
     * @return \Evp\Bundle\TicketBundle\Entity\DiscountType[]
     */
    public function getAllActiveByTicketTypeAndScope(TicketType $tt, $sc) {
        return $this->createQueryBuilder('dt')
            ->join('dt.ticketTypes', 'tt')
            ->andWhere('tt.id = :ttid')
            ->andWhere('dt.scope = :sc')
            ->andWhere('dt.status = :on')
            ->andWhere('dt.dateStarts <= :now')
            ->andWhere('dt.dateEnds >= :now')
            ->setParameters(
                array(
                    'ttid' => $tt->getId(),
                    'on' => 1,
                    'now' => new \DateTime,
                    'sc' => $sc,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all active DiscountTypes by User and Scope
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @param string $sc
     * @return \Evp\Bundle\TicketBundle\Entity\DiscountType[]
     */
    public function getAllActiveByUserAndScope(User $us, $sc) {
        $orderDetails = $this->_em->createQueryBuilder()
            ->select('od')
            ->from('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails', 'od')
            ->where('od.user = :us')
            ->setParameters(
                array(
                    'us' => $us,
                )
            )
            ->getQuery()
            ->getResult();

        $discountTypes = array();
        foreach ($orderDetails as $orderDetail) {
            $ticketType = $orderDetail->getTicketType();
            $discountTypes = array_merge(
                $this->getAllActiveByTicketTypeAndScope($ticketType, $sc),
                $discountTypes
            );
        }

        $discountTypeIds = array();
        foreach ($discountTypes as $discountType) {
            $discountTypeIds[] = $discountType->getId();
        }

        if (empty($discountTypeIds)) {
            return array();
        }
        return $this->_em->createQueryBuilder()
            ->select('dt')
            ->from('Evp\Bundle\TicketBundle\Entity\DiscountType', 'dt')
            ->where('dt.id IN (:id)')
            ->setParameters(
                array(
                    'id' => $discountTypeIds,
                )
            )
            ->getQuery()
            ->getResult();
    }


    /**
     * Gets the discountType that is attached to the ticket type via TicketTypeDiscount
     *
     * @param TicketType $ticketType
     * @param string $token
     * @return \Evp\Bundle\TicketBundle\Entity\DiscountType
     */
    public function getOneByTicketTypeAndToken(TicketType $ticketType, $token)
    {
        return $this->createQueryBuilder('dt')
            ->join('dt.ticketTypes', 'tt')
            ->join('dt.discounts', 'd')
            ->andWhere('tt.id = :ttId')
            ->andWhere('d.token = :tok')
            ->andWhere('d.status = :stat')
            ->setParameters(array(
                    'ttId' => $ticketType->getId(),
                    'tok' => $token,
                    'stat' => Discount::STATUS_AVAILABLE,
                ))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
