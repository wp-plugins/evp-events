<?php
/**
 * Gets the Ticket token from a Pool
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\TicketTokenProvider;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;

/**
 * Class PoolTicketTokenProvider
 */
class PoolTicketTokenProvider extends TicketTokenProviderAbstract implements TicketTokenProviderInterface
{
    const TOKEN_REPO = 'EvpTicketBundle:TicketToken';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param Event $event
     * @param Ticket $ticket
     *
     * @return null|string
     */
    public function generateTokenForTicketByEvent(Ticket $ticket, Event $event)
    {
        $ticketToken = $this->entityManager->getRepository(self::TOKEN_REPO)
            ->getUnusedTokenForEvent($event);

        if (!empty($ticketToken)) {
            $token = $ticketToken->getToken();
            $ticketToken
                ->setUsed(true)
                ->setTicket($ticket);

            return $token;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function unbindTokenByTicket($token, Ticket $ticket)
    {
        $ticketToken = $this->entityManager->getRepository(self::TOKEN_REPO)
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );
        if (empty($ticketToken)) {
            return false;
        }

        $ticketToken
            ->setTicket(null)
            ->setUsed(false);
        $ticket->setToken(null);

        $this->entityManager->flush();
        return true;
    }
} 
