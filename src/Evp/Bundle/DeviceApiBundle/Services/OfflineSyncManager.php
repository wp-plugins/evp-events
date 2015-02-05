<?php
/**
 * Offline sync manager for response & request preparation for offline DB sync
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\DeviceApiBundle\Services;

use Evp\Bundle\DeviceApiBundle\Entity\ApiTicket;
use Evp\Bundle\DeviceApiBundle\Entity\ApiUsedTicket;
use Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use FOS\RestBundle\View\ViewHandler;
use JMS\Serializer\SerializerBuilder;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OfflineSyncManager
 */
class OfflineSyncManager {

    const TWIG_REGEX_BEGIN = '/({{[ ?]+([';
    const TWIG_REGEX_END = ']+)[ ?]+}})/';
    const REPLACED_TAG_WRAP = '%';

    /**
     * @var array
     */
    static private $timestampableKeys = array(
        'from_date',
    );

    /**
     * @var \Evp\Bundle\TicketBundle\Service\TicketManager
     */
    private $ticketManager;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;

    /**
     * @var \stdClass
     */
    private $result;

    /**
     * @var \FOS\RestBundle\View\ViewHandler
     */
    private $viewHandler;

    /**
     * @var \FOS\RestBundle\View\View
     */
    private $jsonView;

    /**
     * @var \Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer
     */
    private $ticketExaminer;

    /**
     * @var TicketTokenParser
     */
    private $tokenParser;

    /**
     * @var int
     */
    private $statusCode = 200;

    /**
     * @var array
     */
    private $templateTagMap;

    /**
     * @var array
     */
    private $templatesToParse;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader
     */
    private $twigLoader;

    /**
     * @var \HTMLPurifier
     */
    private $purifier;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $utcOffset;

    /**
     * @param TicketManager $tm
     * @param \Monolog\Logger $log
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $rt
     * @param \FOS\RestBundle\View\ViewHandler $vh
     * @param TicketTokenParser $tp
     * @param array $tagMap
     * @param \Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader $tw
     * @param array $templates
     * @param \HTMLPurifier $purifier
     * @param string $utcOffset
     */
    public function __construct(
        TicketManager $tm,
        Logger $log,
        Router $rt,
        ViewHandler $vh,
        TicketTokenParser $tp,
        $tagMap,
        DatabaseTwigLoader $tw,
        $templates,
        \HTMLPurifier $purifier,
        $utcOffset
    ) {
        $this->ticketManager = $tm;
        $this->logger = $log;
        $this->router = $rt;
        $this->viewHandler = $vh;
        $this->tokenParser = $tp;
        $this->templateTagMap = $tagMap;
        $this->twigLoader = $tw;
        $this->templatesToParse = $templates;
        $this->purifier = $purifier;

        $this->serializer = SerializerBuilder::create()->build();
        $this->utcOffset = $utcOffset;
    }

    /**
     * Gets the Tickets by given Filters
     *
     * @param ParameterBag $filters
     * @return self
     */
    public function prepareOfflineData(ParameterBag $filters) {
//        $filters = $this->normalizeToUtcTime($filters);
        $this->logger->addDebug('offline sync requested with data', array($filters->all()));
        $this->result = new \stdClass;

        $this->result->available = $this->ticketManager->getTicketsForOfflineSync($filters, Ticket::STATUS_UNUSED);
        $this->result->used = $this->ticketManager->getTicketsForOfflineSync($filters, Ticket::STATUS_USED);

        $events = $this->ticketManager->getEventsBySyncFilters();
        $this->buildExaminerTexts($events);
        $this->buildMetadata($filters);
        return $this;
    }

    /**
     * Normalizes (removes) utcOffset from Filter key where utcOffset was applied in previous response
     *
     * @param ParameterBag $filters
     * @return ParameterBag
     */
    private function normalizeToUtcTime(ParameterBag $filters) {
        $arr = $filters->all();
        $keys = array_keys($arr);
        foreach ($keys as $key) {
            if (in_array($key, self::$timestampableKeys)) {
                $this->logger->debug('found timestampable API filter', array($key, $arr[$key]));
                $modifiedTime = (int)$arr[$key] - ((int)$this->utcOffset * 3600);
                $filters->set($key, $modifiedTime);
                $this->logger->debug('replacing timestampable API filter with removed utcOffset', array($key, $modifiedTime));
            }
        }
        return $filters;
    }

    /**
     * Builds FOS View on Offline data result based on format
     *
     * @param string $format
     * @return self
     */
    public function buildView($format = 'json') {
        $this->recreateResults();
        $jsonResult = array();
        foreach ($this->result as $name => $data) {
            $jsonResult[$name] =  $this->reserializeData($data, $name, $format);
        }
        $this->jsonView = $this->serializer->serialize($jsonResult, $format);
        return $this;
    }

    /**
     * Returns the Json Response
     *
     * @return Response
     */
    public function getResponse() {
        return new Response(
            $this->jsonView,
            200,
            array(
                'Content-Type' => 'application/json',
            )
        );
    }

    /**
     * Marks used tickets into DB
     *
     * @param array $hashes
     * @return self
     */
    public function markUsedTickets($hashes) {
        $toDelete = count($hashes);
        $deleted = 0;
        foreach ($hashes as $hash) {
            try {
                $this->logger->addDebug('Got Ticket hash for', array($hash));
                $token = $this->tokenParser->parseFromBase64Url($hash);
                $this->logger->addDebug('Marking ticket as used from offline sync', array($token));
                $ticket = $this->ticketManager->getTicketByToken($token);
                $this->ticketManager->markAsUsed($ticket, $this->ticketExaminer);
            } catch (\Exception $e) {
                $this->logger->addDebug('Failed to mark ticket as used from offline sync', array($hash, $e->getMessage()));
                continue;
            }
            $deleted++;
        }
        if ($toDelete !== $deleted) {
            $this->statusCode = 409;
        }
        return $this;
    }

    /**
     * @param TicketExaminer $ex
     * @return self
     */
    public function setExaminer(TicketExaminer $ex) {
        $this->ticketExaminer = $ex;
        return $this;
    }

    /**
     * Gets the HTTP status code of last operation
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Builds Result metadata
     *
     * @param ParameterBag $filters
     */
    private function buildMetadata(ParameterBag $filters) {
        $_metadata = new \stdClass;

        $_metadata->total_available = count($this->result->available);
        $_metadata->total_used = count($this->result->used);

        foreach ($filters as $name => $filter) {
            $_metadata->$name = $filter;
        }
        $_metadata->last_date = $this->extractLastTicketTimestamp();
        $this->result->_metadata = $_metadata;

        $this->logger->addDebug('offline sync built with metadata', array($_metadata));
    }

    /**
     * Returns last Ticket dateModified
     *
     * @return int
     */
    private function extractLastTicketTimestamp() {
        $time = new \DateTime;
        $time->setTimestamp(0);
        foreach ($this->result->available as $ticket) {
            if ($ticket->getDateModified() instanceof \DateTime) {
                if ($ticket->getDateModified()->getTimestamp() > $time->getTimestamp()) {
                    $time->setTimestamp($ticket->getDateModified()->getTimestamp());
                }
            }
        }
        return $time->getTimestamp();
    }

    /**
     * Builds Examiner texts for each given Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event[] $events
     */
    private function buildExaminerTexts($events) {
        foreach ($events as $event) {
            $text = new \stdClass;
            $text->event_id = $event->getId();
            $text->event_name = $event->getName();
            $text->date_ends = $event->getDateEnds()->getTimestamp();
            foreach ($this->templatesToParse as $key => $name) {
                $text->$key = $this->retokenizeTwigTemplate($event, $key, $name);
            }
            $this->result->texts[] = $text;
        }
    }

    /**
     * Retokenizes given twig template by replacing tag in TagMap
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param string $key
     * @param string $name
     * @return string
     */
    private function retokenizeTwigTemplate(Event $event, $key, $name) {
        $source = null;
        try {
            $source = $this->twigLoader
                ->setObject($event)
                ->setType($key)
                ->getSource($name);
        } catch (\Twig_Error_Loader $e) {
            $this->logger->error('Failed to get Twig template for Event', array($e));
            return '';
        }

        $this->logger->debug('Examiner template source', array($source));
        $pure = $this->purifier->purify($source);
        foreach ($this->templateTagMap as $name => $tag) {
            $pure = preg_replace(self::TWIG_REGEX_BEGIN .$tag .self::TWIG_REGEX_END, "%$name%", $pure);
        }
        $this->logger->debug('Retokenized examiner template source', array($pure));
        return $pure;
    }

    /**
     * Recreates the results for Json response
     */
    private function recreateResults() {
        $this->logger->debug('Recreating API ticket instances');
        foreach ($this->result->available as $key => $ticket) {
            $availableTicket = ApiTicket::createFromTicket($ticket);
            $availableTicket->setCodeContents($this->router->generate(
                    'ticket_checker',
                    array('ticketToken' => $ticket->getToken()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ));
            $this->result->available[$key] = $availableTicket;
        }
        foreach ($this->result->used as $key => $ticket) {
            $usedTicket = ApiUsedTicket::createFromTicket($ticket);
            $usedTicket->setCodeContents($this->router->generate(
                    'ticket_checker',
                    array('ticketToken' => $ticket->getToken()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ));
            $this->result->used[$key] = $usedTicket;
        }
    }

    /**
     * Reserializes data based on Entity Annotations
     *
     * @param mixed $data
     * @param string $name
     * @param string $format
     * @return mixed
     */
    private function reserializeData($data, $name, $format) {
        switch ($name) {
            case 'texts':
            case '_metadata':
                $data = $this->serializer->deserialize(json_encode($data), 'array', $format);
                break;
            default:
                $data = $this->serializer->deserialize(
                    $this->serializer->serialize($data, $format),
                    'array',
                    $format
                );
                break;
        }
        return $data;
    }
}
