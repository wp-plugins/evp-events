<?php
/**
 * Invoice Mail strategy - for sending tickets
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\MailStrategy;

use Evp\Bundle\TicketBundle\Service\TwigTemplateManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class InvoiceStrategy
 */
class InvoiceStrategy extends StrategyAbstract implements StrategyInterface {

    const MAIL_SUBJECT_INVOICE = 'mail.subject.invoice_email';
    const INVOICE_FINAL_TEMPLATE = 'invoice_final.html.twig';
    const INVOICE_PROFORMA_TEMPLATE = 'invoice_proforma.html.twig';

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $bodyType;

    /**
     * Generates the Swift_Message for Invoice by Order token
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
        $invoice = $order->getInvoice();

        $twigEnv = $this->twigDbLoader
            ->setObject($order->getEvent())
            ->setType($this->bodyType);

        $template = $twigEnv->getTemplateEntity($this->template);
        $this->twig = $twigEnv->getTwig();

        $body = $this->twig->render($this->templateName);
        $this->message
            ->setSubject($template->getSubject())
            ->setTo($invoice->getUser()->getEmail())
            ->setFrom($this->parseSender($template->getFromEmail()))
            ->attach(
                $this->htmlConverter->addAttachment(
                    $this->router->generate(
                        'evp_print_invoice',
                        array(
                            'token' => $invoice->getOrder()->getToken(),
                            'type' => $this->templateType,
                        ),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    $template->getAttachmentName() .' #' .$invoice->getId() .'.pdf'
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

    /**
     * {@inheritdoc}
     *
     * @param string $type
     *
     * @return StrategyInterface
     */
    public function setTemplateType($type) {
        if ($type == StrategyInterface::INVOICE_FINAL) {
            $this->templateType = $type;
            $this->templateName = self::INVOICE_FINAL_TEMPLATE;
            $this->bodyType = TwigTemplateManager::TYPE_INVOICE_FINAL_EMAIL;
        }
        if ($type == StrategyInterface::INVOICE_PROFORMA) {
            $this->templateType = $type;
            $this->templateName = self::INVOICE_PROFORMA_TEMPLATE;
            $this->bodyType = TwigTemplateManager::TYPE_INVOICE_PROFORMA_EMAIL;
        }
        return $this;
    }
}
