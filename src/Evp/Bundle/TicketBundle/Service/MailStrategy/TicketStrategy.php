<?php
/**
 * Ticket Mail strategy - for sending tickets
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\MailStrategy;

use Evp\Bundle\TicketBundle\Service\TwigTemplateManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TicketStrategy
 */
class TicketStrategy extends StrategyAbstract implements StrategyInterface {

    const MAIL_SUBJECT_TICKET = 'mail.subject.ticket_email';
    const MAIL_BODY_TICKET = 'ticket.html.twig';

    /**
     * @var string
     */
    private $template;

    /**
     * Generates the Swift_Message by Ticket token
     *
     * @param string $token
     * @return \Swift_Message
     */
    public function generateMessage($token) {
        $ticket = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );

        $twigEnv = $this->twigDbLoader
            ->setObject($ticket)
            ->setType(TwigTemplateManager::TYPE_TICKET_EMAIL);

        $template = $twigEnv->getTemplateEntity($this->template);
        $this->twig = $twigEnv->getTwig();

        $body = $this->twig->render(self::MAIL_BODY_TICKET);
        $this->message
            ->setSubject($template->getSubject())
            ->setTo($ticket->getUser()->getEmail())
            ->setFrom($this->parseSender($template->getFromEmail()))
            ->attach(
                $this->htmlConverter->addAttachment(
                    $this->router->generate(
                        'evp_print_ticket',
                        array('token' => $ticket->getToken()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    $template->getAttachmentName() . $this->getUniqueSuffix() . '.pdf'
                )
            )
            ->setBody($this->htmlConverter->updateBody($body), 'text/html');

        return $this->message;
    }

    /**
     * Sets the template
     * @param string $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }
} 
