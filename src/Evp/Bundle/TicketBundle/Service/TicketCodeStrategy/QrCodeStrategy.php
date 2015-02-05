<?php
namespace Evp\Bundle\TicketBundle\Service\TicketCodeStrategy;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use QrCode;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class QrCodeStrategy
 *
 * Generates a qr code image
 */
class QrCodeStrategy implements TicketCodeStrategyInterface
{
    /**
     * @var int
     */
    private $size;
    /**
     * @var int
     */
    private $margin;
    /**
     * @var string
     */
    private $pathToTmp;
    /**
     * @var int
     */
    private $errorCorrectLevel = QR_ECLEVEL_L;
    /**
     * @var string
     */
    private $ticketCheckerRouteName;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var string
     */
    private $deviceAttacher;

    /**
     * @param array $options
     * @param Router $router
     */
    function __construct(
        array $options,
        Router $router
    )
    {
        $this->size = $options['size'];
        $this->margin = $options['margin'];
        $this->pathToTmp = $options['path_to_tmp'];
        $this->ticketCheckerRouteName = $options['ticket_checker_route_name'];
        $this->deviceAttacher = $options['ticket_attach_device_router_name'];

        $this->router = $router;
    }

    /**
     * @param Ticket $ticket
     * @return string
     */
    public function createFromTicket(Ticket $ticket) {
        $tmpFile = tempnam($this->pathToTmp, 'qr');
        $checkerUri = $this->buildCheckerUriFromTicket($ticket);

        QRcode::png(
            $checkerUri,
            $tmpFile,
            $this->errorCorrectLevel,
            $this->size,
            $this->margin
        );

        return file_get_contents($tmpFile);
    }

    /**
     * Creates QR code for attaching device
     * @param Event $event
     * @return string
     */
    public function createFromEvent(Event $event) {
        $tmpFile = tempnam($this->pathToTmp, 'qr');
        $url = $this->buildAttacherUrlFromEvent($event);

        QRcode::png(
            $url,
            $tmpFile,
            $this->errorCorrectLevel,
            $this->size,
            $this->margin
        );

        return file_get_contents($tmpFile);
    }

    /**
     * Encodes arbitrary text inside of a qr code
     * @param string $text
     * @return string
     */
    public function createFromString($text) {
        $tmpFile = tempnam($this->pathToTmp, 'qr');

        QRcode::png(
            $text,
            $tmpFile,
            $this->errorCorrectLevel,
            $this->size,
            $this->margin
        );

        return file_get_contents($tmpFile);
    }

    /**
     * @param int $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
    }

    /**
     * @return int
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * @param string $pathToTmp
     */
    public function setPathToTmp($pathToTmp)
    {
        $this->pathToTmp = $pathToTmp;
    }

    /**
     * @return string
     */
    public function getPathToTmp()
    {
        return $this->pathToTmp;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $errorCorrectLevel
     */
    public function setErrorCorrectLevel($errorCorrectLevel)
    {
        $this->errorCorrectLevel = $errorCorrectLevel;
    }

    /**
     * @return int
     */
    public function getErrorCorrectLevel()
    {
        return $this->errorCorrectLevel;
    }

    /**
     * @param string $checkerRoute
     */
    public function setCheckerRoute($checkerRoute)
    {
        $this->ticketCheckerRouteName = $checkerRoute;
    }

    /**
     * @return string
     */
    public function getCheckerRoute()
    {
        return $this->ticketCheckerRouteName;
    }

    /**
     * @param Ticket $ticket
     * @return string
     */
    protected function buildCheckerUriFromTicket(Ticket $ticket)
    {
        $pathToChecker = $this->router->generate(
            $this->ticketCheckerRouteName,
            array(
                'ticketToken' => $ticket->getToken()
            ),
            true
        );
        return $pathToChecker;
    }

    /**
     * @param Event $event
     * @return string
     */
    protected function buildAttacherUrlFromEvent(Event $event) {
        return $this->router->generate(
            $this->deviceAttacher,
            array('token' => $event->getToken()),
            true
        );
    }
}
