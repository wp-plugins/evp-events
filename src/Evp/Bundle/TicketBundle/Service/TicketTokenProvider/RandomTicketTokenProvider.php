<?php
/**
 * Generates random token for Ticket
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\TicketTokenProvider;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketMaintenanceBundle\Services\UniqueTokenGenerator;

/**
 * Class RandomTicketTokenProvider
 */
class RandomTicketTokenProvider extends TicketTokenProviderAbstract implements TicketTokenProviderInterface
{
    const TOKEN_FIELD = 'token';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UniqueTokenGenerator
     */
    private $generator;

    /**
     * @param EntityManager                                                     $em
     * @param \Evp\Bundle\TicketMaintenanceBundle\Services\UniqueTokenGenerator $generator
     */
    public function __construct(
        EntityManager $em,
        UniqueTokenGenerator $generator
    ) {
        $this->entityManager = $em;
        $this->generator = $generator;
        $this->generator->setEntityManager($em);
    }

    /**
     * {@inheritdoc}
     *
     * @param Ticket $ticket
     * @param Event  $event
     *
     * @return string|null
     */
    public function generateTokenForTicketByEvent(Ticket $ticket, Event $event)
    {
        $token = $this->generator->generateForEntityAndField(
            new Ticket,
            self::TOKEN_FIELD
        );
        return $token;
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
        // no need to free-up token
        return true;
    }
} 
