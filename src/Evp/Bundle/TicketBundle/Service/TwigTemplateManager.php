<?php
/**
 * Manages Twig templates
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

/**
 * Class TwigTemplateManager
 */
class TwigTemplateManager extends ManagerAbstract {

    const TEMPLATE_ENTITY = 'Evp\Bundle\TicketBundle\Entity\Template';
    const PARENT_CLASS = 'Evp\Bundle\TicketBundle\Entity\Event';
    const TYPE_CUSTOM = 'custom';
    const TYPE_TICKET = 'ticket';
    const TYPE_INVOICE_FINAL = 'invoice_final';
    const TYPE_INVOICE_PROFORMA = 'invoice_proforma';
    const TYPE_TICKET_EMAIL = 'ticket_email';
    const TYPE_INVOICE_FINAL_EMAIL = 'invoice_final_email';
    const TYPE_INVOICE_PROFORMA_EMAIL = 'invoice_proforma_email';
    const TYPE_TICKET_USED = 'ticket_used';
    const TYPE_TICKET_UNUSED = 'ticket_unused';

    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $name;

    /**
     * @var object
     */
    private $template;

    /**
     * @var string
     */
    private $type = null;

    /**
     * Sets object to print
     *
     * @param $obj
     * @return self
     */
    public function setObject($obj) {
        $this->object = $obj;
        return $this;
    }

    /**
     * Set current template type
     *
     * @param string $str
     * @return self
     */
    public function setType($str) {
        $this->type = $str;
        return $this;
    }

    /**
     * Finds and gets the template Entity
     * @param string $name
     * @return object
     */
    public function getTemplate($name = null) {
        $this->logger->debug('Trying to get template with name ' .$name);
        $this->name = $name;
        if (!empty($this->object)) {
            $this->extractTemplate();
            if (empty($this->template)) {
                $this->extractCustomTemplate();
            }
        } else {
            $this->extractTemplateByName();
        }

        return $this->template;
    }

    /**
     * Tries to get Custom template only by name
     */
    private function extractTemplateByName() {
        $this->template = $this->entityManager->getRepository(self::TEMPLATE_ENTITY)
            ->findOneBy(
                array(
                    'name' => $this->name,
                    'type' => 'custom',
                )
            );
        return;
    }

    /**
     * Gets the Twig template source for given Entity and optional Name
     *
     * @param string $name
     * @throws \Twig_Error_Loader
     * @return string
     */
    public function getTemplateSource($name = null) {
        $this->getTemplate($name);
        if (!empty($this->template)) {
            $this->logger->addDebug(
                'twig DB template found',
                array(
                    'id' => $this->template->getId(),
                )
            );
            return $this->template->getSource();
        }
        throw new \Twig_Error_Loader("Template \"$name\" not found in Database");
    }

    /**
     * Extracts the Template Entity by given params
     */
    private function extractTemplate() {
        $object = $this->object;
        $this->logger->debug('Trying to extract template for object', array($object));
        if ($this->object instanceof \Evp\Bundle\TicketBundle\Entity\Ticket) {
            if ($this->object->getEvent()->getGlobalEntityTemplate()) {
                $this->object = $this->object->getEvent();
            } else {
                $this->extract($object->getTicketType());
                if ($this->template) {
                    $this->object = $object->getTicketType();
                    return;
                } else {
                    $this->extract($object->getEvent());
                    $this->object = $object->getEvent();
                    return;
                }

            }
        }
        if ($this->object instanceof \Evp\Bundle\TicketBundle\Entity\Event) {
            $this->extract();
            return;
        }
    }

    /**
     * Gets the Template
     *
     * @param object $obj
     */
    private function extract($obj = null) {
        if(!$obj) {
            $obj = $this->object;
        }
        $params = array(
            'parentClass' => $this->entityManager->getClassMetadata(get_class($obj))->name,
            'foreignKey' => $obj->getId(),
            'name' => $this->name,
        );
        if (!empty($this->type)) {
            $params['type'] = $this->type;
        }
        $this->logger->debug('Fetching template from Database with params', $params);
        $this->template = $this->entityManager->getRepository(self::TEMPLATE_ENTITY)
            ->findOneBy($params);
        return;
    }

    /**
     * Tries to find custom template
     */
    private function extractCustomTemplate() {
        $params = array(
            'parentClass' => $this->entityManager->getClassMetadata(get_class($this->object))->name,
            'foreignKey' => $this->object->getId(),
            'name' => $this->name,
            'type' => self::TYPE_CUSTOM,
        );
        $this->template = $this->entityManager->getRepository(self::TEMPLATE_ENTITY)
            ->findOneBy($params);
        return;
    }
}
