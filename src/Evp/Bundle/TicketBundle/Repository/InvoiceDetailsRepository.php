<?php

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class InvoiceDetailsRepository
 */
class InvoiceDetailsRepository extends EntityRepository
{
    /**
     * @return int
     */
    public function getNextInvoiceNumber()
    {
        $res = $this->createQueryBuilder('i')
            ->select('MAX(i.number)')
            ->getQuery()
            ->getSingleScalarResult()
            ;

        if ($res === null) {
            $res = 0;
        }

        return ++$res;
    }
}
