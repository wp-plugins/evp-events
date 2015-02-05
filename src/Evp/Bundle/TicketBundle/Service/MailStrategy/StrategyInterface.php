<?php
/**
 * Mail StrategyInterface for common methods
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\MailStrategy;

/**
 * Interface StrategyInterface
 */
interface StrategyInterface
{
    const INVOICE_FINAL = 'invoice_final';
    const INVOICE_PROFORMA = 'invoice_proforma';

    /**
     * Generates Swift_Message for given Entity token
     *
     * @param string $token
     * @return \Swift_Message
     */
    function generateMessage($token);

    /**
     * Sets Twig template name
     *
     * @param string $string
     */
    function setTemplate($string);
}
