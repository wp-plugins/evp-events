<?php
/**
 * Tickets Mail strategy - for sending All Order Tickets
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\MailStrategy;

use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Service\TwigTemplateManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class MultipleTicketsStrategy
 */
class MultipleTicketsStrategy extends StrategyAbstract implements StrategyInterface {

    const MAIL_SUBJECT_TICKETS = 'mail.subject.tickets_email';
    const MAIL_BODY_TICKETS = 'ticket.html.twig';

    /**
     * @var string
     */
    private $template;

    /**
     * Generates the Swift_Message by Order token
     *
     * @param string $token
     * @return \Swift_Message
     */
    public function generateMessage($token) {
        $order = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Order')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );

        $twigEnv = $this->twigDbLoader
            ->setObject($order->getEvent())
            ->setType(TwigTemplateManager::TYPE_TICKET_EMAIL);

        $template = $twigEnv->getTemplateEntity($this->template);
        $this->twig = $twigEnv->getTwig();

        $body = $this->twig->render(self::MAIL_BODY_TICKETS);
        $this->message
            ->setSubject($template->getSubject())
            ->setTo($order->getUser()->getEmail())
            ->setFrom($this->parseSender($template->getFromEmail()))
            ->setBody($body);

        foreach ($order->getUser()->getTickets() as $ticket) {
            $this->message
                ->attach(
                    $this->htmlConverter->addAttachment(
                        $this->router->generate(
                            'evp_print_ticket',
                            array('token' => $ticket->getToken()),
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                        $template->getAttachmentName() . $this->getUniqueSuffix() .'.pdf'
                    )
                )
                ->setBody($this->htmlConverter->updateBody($this->message->getBody($body)), 'text/html');
        }

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
