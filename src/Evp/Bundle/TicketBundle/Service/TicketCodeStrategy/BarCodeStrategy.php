<?php
/**
 * Generates Ticket code as barcode
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\TicketCodeStrategy;

use emberlabs\Barcode\Code128;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;

/**
 * Class BarCodeStrategy
 */
class BarCodeStrategy implements TicketCodeStrategyInterface
{
    const WIDTH = 300;
    const HEIGHT = 200;
    const IMG_TYPE = 'png';

    /**
     * Creates Code image for given Ticket
     *
     * @param Ticket $ticket
     *
     * @throws \Exception
     * @return string
     */
    function createFromTicket(Ticket $ticket)
    {
        return $this->createFromString($ticket->getToken());
    }

    /**
     * Creates Code image for given Event
     *
     * @param Event $event
     *
     * @throws \Exception
     * @return string
     */
    function createFromEvent(Event $event)
    {
        return $this->createFromString($event->getToken());
    }

    /**
     * Creates Code image for given string
     *
     * @param string $string
     *
     * @throws \Exception
     * @return string
     */
    function createFromString($string)
    {
        $barcode = new Code128();
        $barcode->setDimensions(self::WIDTH, self::HEIGHT);
        $barcode->setData($string);
        $barcode->draw();

        $file = tempnam(sys_get_temp_dir(), 'bar') .'.' .self::IMG_TYPE;
        $barcode->save($file);

        $contents = file_get_contents($file);
        unlink($file);

        return $contents;
    }
}
