<?php
/**
 * Abstract class for TicketToken providers
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\TicketTokenProvider;

/**
 * Class TicketTokenProviderAbstract
 */
abstract class TicketTokenProviderAbstract implements TicketTokenProviderInterface
{
    /**
     * @var int
     */
    private $priority;

    /**
     * {@inheritdoc}
     *
     * @param string $priority
     */
    public function setPriority($priority)
    {
        $this->priority = (int)$priority;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
} 
