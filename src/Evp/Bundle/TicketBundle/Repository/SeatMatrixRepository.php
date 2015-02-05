<?php
/**
 * Seat Matrix Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Seat\Area;

/**
 * Class SeatMatrixRepository
 */
class SeatMatrixRepository extends EntityRepository {

    /**
     * Gets the Matrix dimensions by Area
     *
     * @param Area $ar
     * @param string $dm
     * @return int
     */
    public function getAreaMatrixDimension(Area $ar, $dm) {
        return $this->_em->createQueryBuilder()
            ->select("MAX(m.$dm)")
            ->from('Evp\Bundle\TicketBundle\Entity\Seat\Matrix', 'm')
            ->where('m.area= :ar')
            ->setParameters(
                array(
                    'ar' => $ar,
                )
            )
            ->getQuery()
            ->getSingleScalarResult();
    }
} 