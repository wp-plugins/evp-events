<?php
/**
 * MailManager for various Email related actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Bridge\Twig\TwigEngine;

/**
 * Class MailManager
 */
class MailManager extends ManagerAbstract {

    const MAIL_TYPE_INVOICE_PROFORMA = 'invoice_proforma';
    const MAIL_TYPE_INVOICE_FINAL = 'invoice_final';
    const MAIL_TYPE_TICKET = 'ticket';
    const MAIL_TYPE_MULTIPLE_TICKETS = 'multipleTickets';

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string[]
     */
    private $templates;

    /**
     * @var \Symfony\Bridge\Twig\TwigEngine
     */
    private $twig;

    /**
     * @var MailStrategy\StrategyInterface[]
     */
    private $strategies;

    /**
     * @var \Swift_Message
     */
    private $message;

    /**
     * @var int
     */
    private $senderResult;

    /**
     * @var string
     */
    private $type = '';

    /**
     * Sets the requirements
     *
     * @param EntityManager $entityManager
     * @param Logger $logger
     * @param $templates
     * @param \Swift_Mailer $mailer
     * @param \Evp\Bundle\TicketBundle\Service\MailStrategy\StrategyInterface[] $strategies
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        $templates,
        \Swift_Mailer $mailer,
        $strategies
    ) {
        parent::__construct($entityManager, $logger);
        $this->templates = $templates;
        $this->strategies = $strategies;
        $this->mailer = $mailer;
    }

    /**
     * Generates Swift_Message by given type string & according Entity
     *
     * @param string $type
     * @param string $token
     *
     * @return self
     */
    public function prepareMessage($type, $token) {
        $this->strategies[$type]->setTemplate($this->templates[$type]);
        if ($type == self::MAIL_TYPE_INVOICE_FINAL || $type == self::MAIL_TYPE_INVOICE_PROFORMA) {
            $this->message = $this->strategies[$type]
                ->setTemplateType($this->type)
                ->generateMessage($token);
        } else {
            $this->message = $this->strategies[$type]->generateMessage($token);
        }
        return $this;
    }

    /**
     * Sets the Template Type, if applicable
     *
     * @param string $tmplType
     * @return self
     */
    public function setTemplateType($tmplType) {
        $this->type = $tmplType;
        return $this;
    }

    /**
     * Sends current Message
     * @return self
     */
    public function sendMessage() {
        $this->senderResult = $this->mailer->send($this->message);
        return $this;
    }

    /**
     * Returns the Swift_Mailer result (number of messages sent)
     * @return int
     */
    public function getResult() {
        return $this->senderResult;
    }

    /**
     * Tests given settings for connectivity
     *
     * @param $settings
     *
     * @return bool
     */
    public function testSettings($settings)
    {
        if ($settings['transport'] === 'gmail') {
            $settings['encryption'] = 'ssl';
            $settings['auth_mode'] = 'login';
            $settings['host'] = 'smtp.gmail.com';
            $settings['transport'] = 'smtp';
            $settings['port'] = 465;
        }
        if (!array_key_exists('encryption', $settings)) {
            $settings['encryption'] = null;
        }
        $this->logger->debug('Trying to test Mailer settings', $settings);

        $transport = null;
        if ($settings['transport'] === 'smtp') {
            $transport = new \Swift_Transport_EsmtpTransport(
                new \Swift_Transport_StreamBuffer( new \Swift_StreamFilters_StringReplacementFilterFactory()),
                array(
                    new \Swift_Transport_Esmtp_AuthHandler(
                        array(
                            new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator,
                            new \Swift_Transport_Esmtp_Auth_LoginAuthenticator,
                            new \Swift_Transport_Esmtp_Auth_PlainAuthenticator,
                        )
                    )
                ),
                new \Swift_Events_SimpleEventDispatcher
            );

            $transport
                ->setHost($settings['host'])
                ->setPort($settings['port'])
                ->setEncryption($settings['encryption'])
                ->setTimeout(30)
                ->setSourceIp(null);
            $transport->setUsername($settings['user']);
            $transport->setPassword($settings['password']);
            $transport->setAuthMode($settings['auth_mode']);
        }

        if ($settings['transport'] === 'sendmail') {
            $transport = new \Swift_Transport_SendmailTransport(
                new \Swift_Transport_StreamBuffer( new \Swift_StreamFilters_StringReplacementFilterFactory()),
                new \Swift_Events_SimpleEventDispatcher
            );
        }
        if ($settings['transport'] === 'mail') {
            $transport = new \Swift_Transport_MailTransport(
                new \Swift_Transport_SimpleMailInvoker,
                new \Swift_Events_SimpleEventDispatcher
            );
        }

        $success = true;
        try {
            $transport->start();
        } catch (\Exception $e) {
            $success = false;
            $this->logger->debug('Mailer settings test failed', array($e));
        }
        return $success;
    }
}
