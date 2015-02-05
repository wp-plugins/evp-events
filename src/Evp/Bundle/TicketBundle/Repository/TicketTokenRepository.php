<?php
namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\TicketToken;


/**
 * Class TicketTokenRepository
 */
class TicketTokenRepository extends EntityRepository
{
    /**
     * @param Event $ev
     *
     * @return TicketToken|null
     */
    public function getUnusedTokenForEvent(Event $ev)
    {
        return $this->createQueryBuilder('tt')
            ->where('tt.event = :ev')
            ->andWhere('tt.used = false')
            ->setMaxResults(1)
            ->setParameters(array(
                    'ev' => $ev,
                ))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
