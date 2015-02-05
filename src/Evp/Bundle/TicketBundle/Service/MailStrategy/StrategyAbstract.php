<?php
/**
 * Abstract class for Mail Strategies with all necessary dependencies
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\MailStrategy;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader;
use Evp\Bundle\TicketBundle\Service\HtmlConvert\HtmlConvertInterface;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\Translator;

/**
 * Class StrategyAbstract
 */
abstract class StrategyAbstract {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader
     */
    protected $twigDbLoader;

    /**
     * @var \Swift_Message
     */
    protected $message;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $sender = 'tickets@evp.lt';

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var bool
     */
    protected $shellEnabled;

    /**
     * @var string
     */
    protected $templateType = '';

    /**
     * @var HtmlConvertInterface
     */
    protected $htmlConverter;

    /**
     * Sets common dependencies
     *
     * @param EntityManager                                                     $em
     * @param Logger                                                            $log
     * @param \Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader               $twigLoader
     * @param Router                                                            $router
     * @param Translator                                                        $translator
     * @param \Evp\Bundle\TicketBundle\Service\HtmlConvert\HtmlConvertInterface $converter
     */
    public function __construct(
        EntityManager $em,
        Logger $log,
        DatabaseTwigLoader $twigLoader,
        Router $router,
        Translator $translator,
        HtmlConvertInterface $converter
    ) {
        $this->entityManager = $em;
        $this->logger = $log;
        $this->twigDbLoader = $twigLoader;
        $this->message = \Swift_Message::newInstance();
        $this->router = $router;
        $this->translator = $translator;
        $this->htmlConverter = $converter;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setTemplateType($type) {
        $this->templateType = $type;
        return $this;
    }

    /**
     * Parses sender format "name surname <n.surname@domain.com>" to Swiftmailer format:
     * array('n.surname@domain.com' => 'name surname')
     *
     * @param string $sender
     *
     * @return string|array
     */
    protected function parseSender($sender)
    {
        if (strpos($sender, '<') !== false) {
            $parts = explode('<', trim($sender, '>'));
            $email = trim($parts[1]);
            $name = trim($parts[0]);
            $from[$email] = $name;
            return $from;
        } else {
            return $sender;
        }
    }

    /**
     * @return string
     */
    protected function getUniqueSuffix()
    {
        $now = new \DateTime();
        return '_' . $now->format('YmdHis') . '_' . mt_rand(1000, 9999);
    }
}
