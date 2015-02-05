<?php
/**
 * FormAbstract abstract class for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\Translator;

/**
 * Class FormAbstract
 */
abstract class StepAbstract {
    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    protected $twig;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    protected $user;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    protected $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\TicketManager
     */
    protected $ticketManager;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Prepares valid form
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader $twigLoader
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param \Evp\Bundle\TicketBundle\Service\TicketManager $ticketManager
     */
    public function __construct(
        EntityManager $entityManager,
        DatabaseTwigLoader $twigLoader,
        FormFactory $formFactory,
        TicketManager $ticketManager
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->twig = $twigLoader->getTwig();
        $this->ticketManager = $ticketManager;
    }

    /**
     * @param Logger $log
     */
    public function setLogger(Logger $log)
    {
        $this->logger = $log;
    }

    /**
     * @param \Symfony\Component\Translation\Translator $trans
     */
    public function setTranslator(Translator $trans) {
        $this->translator = $trans;
    }
}
