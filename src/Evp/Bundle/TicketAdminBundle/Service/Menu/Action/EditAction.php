<?php
/**
 * General Edit action for Entities
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

/**
 * Class EditAction
 */
class EditAction extends ActionAbstract implements ActionInterface {

    const PARENTS_NAMESPACE = 'Evp\Bundle\TicketBundle\Entity\\';

    /**
     * @var string
     */
    protected $actionName = 'edit';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var EditCascador\CascadorInterface[]
     */
    private $cascadors;

    /**
     * Sets the configured Cascadors for proper Persist-cascade
     *
     * @param EditCascador\CascadorInterface[] $cascadors
     */
    public function setCascadors($cascadors) {
        $this->cascadors = $cascadors;
    }

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
        return $this;
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
        $formObject = null;
        if (empty($this->targetId)) {
            $formObject = new $this->fqcn;
        } else {
            $formObject = $this->entityManager->getRepository($this->fqcn)
                ->findOneBy(
                    array(
                        'id' => $this->targetId,
                    )
                );
        }
        $formObject = $this->applyParent($formObject);
        $form = $this->formFactory->create($this->form, $formObject);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            if ($flush) {
                $this->entityManager->persist($formObject);
                $this->entityManager->flush();
                if (array_key_exists($this->shortClassName, $this->cascadors)) {
                    $this->cascadors[$this->shortClassName]->cascade($formObject);
                }
            }
            $this->responseType = self::RESPONSE_REDIRECT;
            return true;
        }
        $this->responseType = self::RESPONSE_REGULAR;
        return $form->createView();
    }

    /**
     * Sets the parent for object, if exists
     *
     * @param object $obj
     * @return object
     */
    private function applyParent($obj) {
        if ($this->getParent()) {
            $parent = $this->getParent();
            $parentClass = self::PARENTS_NAMESPACE .ucfirst($parent['class']);
            $parentObj = $this->entityManager->getRepository($parentClass)
                ->findOneBy(
                    array(
                        'id' => $parent['id'],
                    )
                );
            call_user_func(array($obj, 'set' .ucfirst($parent['class'])), $parentObj);
        }
        return $obj;
    }
}
