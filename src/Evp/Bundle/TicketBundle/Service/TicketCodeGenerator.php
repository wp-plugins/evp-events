<?php
/**
 * Generates code image for Ticket using selected Generator service
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Exception\CodeStrategyNotFoundException;
use Evp\Bundle\TicketBundle\Service\TicketCodeStrategy\TicketCodeStrategyInterface;

/**
 * Class TicketCodeGenerator
 * @package Evp\Bundle\TicketBundle\Service
 */
class TicketCodeGenerator
{
    /**
     * @var TicketCodeStrategyInterface[]
     */
    private $codeStrategies;

    /**
     * @var string
     */
    private $currentStrategy;

    /**
     * @param string $currentStrategy
     */
    public function __construct($currentStrategy)
    {
        $this->currentStrategy = $currentStrategy;
    }

    /**
     * @param TicketCodeStrategyInterface $strategy
     * @param string                      $name
     */
    public function addCodeStrategy(TicketCodeStrategyInterface $strategy, $name)
    {
        $this->codeStrategies[$name] = $strategy;
    }

    /**
     * Gets particular Strategy service
     *
     * @param $string
     *
     * @throws \Evp\Bundle\TicketBundle\Exception\CodeStrategyNotFoundException
     * @return TicketCodeStrategyInterface
     */
    public function getStrategy($string)
    {
        if (array_key_exists($string, $this->codeStrategies)) {
            return $this->codeStrategies[$string];
        } else {
            throw new CodeStrategyNotFoundException('Requested strategy \'' .$string .'\' was not found');
        }
    }

    /**
     * @param Ticket $ticket
     *
     * @throws \Evp\Bundle\TicketBundle\Exception\CodeStrategyNotFoundException
     * @return string
     */
    public function createFromTicket(Ticket $ticket)
    {
        if (array_key_exists($this->currentStrategy, $this->codeStrategies)) {
            return $this->codeStrategies[$this->currentStrategy]->createFromTicket($ticket);
        } else {
            throw new CodeStrategyNotFoundException('Current strategy \'' .$this->currentStrategy .'\' was not found');
        }
    }
} 
