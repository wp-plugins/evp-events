<?php
/**
 * Custom Edit action for Templates
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\Templates;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Symfony\Component\Form\FormError;

/**
 * Class EditAction
 */
class EditAction extends ActionAbstract implements ActionInterface {

    const TEMPLATE_ENTITY = 'Evp\Bundle\TicketBundle\Entity\Template';

    /**
     * @var string
     */
    protected $actionName = 'edit';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var object
     */
    private $parentObj;

    /**
     * @var array
     */
    private $templateTypes;

    /**
     * @var array
     */
    private $templateNames;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        $this->fqcn = $params['fqcn'];
        $this->form = $params['form'];
        $this->request = $params['request'];
        $this->templateTypes = $params['templates']['template_types'];
        $this->templateNames = $params['templates']['template_names'];
        $this->parentObj = $params['parent'];

        return $this;
    }

    /**
     * Sets Twig engine
     *
     * @param \Twig_Environment $twig
     */
    public function setTwig (\Twig_Environment $twig) {
        $this->twig = $twig;
    }

    /**
     * Returns the Response type
     *
     * @return string
     */
    public function getResponseType() {
        $this->submitForm(false);
        return $this->responseType;
    }

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName() {
        if ($this->responseType == self::RESPONSE_REGULAR) {
            return $this->templates[$this->actionName];
        }
        if ($this->responseType == self::RESPONSE_REDIRECT) {
            return self::ROUTE_INDEX;
        }
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        $result = $this->submitForm();
        if ($result !== true) {
            return array(
                'form' => $result,
            );
        }
        else {
            return array(
                'menu' => $this->shortClassName,
            );
        }
    }

    /**
     * Submits the form and flushes the Entity
     *
     * @param bool $flush
     * @return bool
     */
    private function submitForm($flush = true) {
        $formObject = $this->entityManager->getRepository(self::TEMPLATE_ENTITY)
            ->findOneBy(
                array(
                    'id' => $this->targetId,
                )
            );
        if (empty($formObject)) {
            $template = self::TEMPLATE_ENTITY;
            $formObject = new $template;
        }

        $form = $this->formFactory->create($this->form, $formObject);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            if (array_key_exists($formObject->getType(), $this->templateNames)) {
                $formObject->setName($this->templateNames[$formObject->getType()]);
            }
            $formObject->setParent($this->parentObj);
            if (!$formObject->getId()) {
                $existingEntity = $this->entityManager->getRepository(self::TEMPLATE_ENTITY)
                    ->findOneBy(
                        array(
                            'parentClass' => $formObject->getParentClass(),
                            'foreignKey' =>$formObject->getForeignKey(),
                            'type' => $formObject->getType(),
                            'name' => $formObject->getName(),
                        )
                    );
                if (!empty($existingEntity)) {
                    $form->addError(new FormError(
                            $this->translator->trans('message.error.template_already_exists', array(), 'validators')
                        ));
                    $this->responseType = self::RESPONSE_REGULAR;
                    return $form->createView();
                }
            }
            try {
                $this->twig->parse($this->twig->tokenize($formObject->getSource()));
            } catch (\Twig_Error_Syntax $e) {
                $form->addError(new FormError($e->getMessage()));
                $this->responseType = self::RESPONSE_REGULAR;
                return $form->createView();
            }
            if ($flush) {
                $this->entityManager->persist($formObject);
                $this->entityManager->flush();
            }
            $this->responseType = self::RESPONSE_REDIRECT;
            return true;
        }
        $this->responseType = self::RESPONSE_REGULAR;
        return $form->createView();
    }
}
