<?php
/**
 * User Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\User;

/**
 * Class UserRepository
 */
class UserRepository extends EntityRepository {

    /**
     * Returns User with non null email
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @return mixed
     */
    public function getUserWithEmail(User $us) {
        return $this->_em->createQueryBuilder()
            ->select('us')
            ->from('Evp\Bundle\TicketBundle\Entity\User', 'us')
            ->where('us.id = :id')
            ->andWhere('us.email IS NOT null')
            ->setParameters(
                array(
                    'id' => $us->getId(),
                )
            )
            ->getQuery()
            ->getResult();
    }
}
