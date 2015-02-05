<?php
/**
 * Toggles Seat Visibility from Admin side
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

/**
 * Class ToggleSeat
 * @package Evp\Bundle\TicketAdminBundle\Service\Ajax
 */
class ToggleSeat extends AjaxAbstract implements AjaxInterface
{
    const SEAT_ENTITY = 'Evp\Bundle\TicketBundle\Entity\Seat\Matrix';

    /**
     * Toggles current Matrix element (seat) Visibility
     *
     * @return string
     */
    public function getResult()
    {
        $seat = $this->entityManager->getRepository(self::SEAT_ENTITY)
            ->findOneBy(
                array(
                    'id' => $this->scopeId,
                )
            );
        if ($seat->getVisible()) {
            $seat->setVisible(false);
        } else {
            $seat->setVisible(true);
        }
        try {
            $this->entityManager->flush($seat);
        } catch (\Exception $e) {
            return 'failed';
        }
        return 'ok';
    }

}

