<?php
/**
 * Edit action for Event & TicketType seat matrix edit
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\Seat;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class EditAction
 */
class EditAction extends ActionAbstract implements ActionInterface {

    const PARENTS_NAMESPACE = 'Evp\Bundle\TicketBundle\Entity\\';

    /**
     * @var string
     */
    private $template = 'EvpTicketAdminBundle:Seat:seatEdit.html.twig';

    /**
     * @var string
     */
    protected $actionName = 'edit';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\SeatManager
     */
    private $seatManager;

    /**
     * @var array
     */
    private $children = array(
        'ticketType',
    );
    /**
     * Sets the necessary managers
     *
     * @param $managers
     */
    public function setManagers($managers) {
        $this->seatManager = $managers['seat_manager'];
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
            return $this->template;
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
        $formObject = $this->entityManager->getRepository($this->fqcn)
            ->findOneBy(
                array(
                    'parentClass' => self::PARENTS_NAMESPACE .ucfirst($this->parent['class']),
                    'foreignKey' => $this->parent['id'],
                )
            );
        if (empty($formObject)) {
            $formObject = new $this->fqcn;
        }
        if (in_array($this->parent['class'], $this->children)) {
            $parentArea = $this->findParentArea();
            if (!empty($parentArea)) {
                $formObject
                    ->setShapeTemplate($parentArea->getShapeTemplate())
                    ->setColumns($parentArea->getColumns())
                    ->setShapeOffsetX($parentArea->getShapeOffsetX())
                    ->setShapeOffsetY($parentArea->getShapeOffsetY());
            }
        }
        $form = $this->formFactory->create($this->form, $formObject);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            if ($flush) {
                $formObject->setParentClass(self::PARENTS_NAMESPACE .ucfirst($this->parent['class']));
                $formObject->setForeignKey($this->parent['id']);
                $this->updateParentAndFlush($formObject);
                $this->seatManager->resizeMatrix($formObject);
            }
            $this->responseType = self::RESPONSE_REDIRECT;
            return true;
        }
        $this->responseType = self::RESPONSE_REGULAR;
        return $form->createView();
    }

    /**
     * Finds parent Area for data inheritance
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Seat\Area
     */
    private function findParentArea() {
        $parent = $this->entityManager->getRepository(self::PARENTS_NAMESPACE .ucfirst($this->parent['class']))
            ->findOneBy(
                array(
                    'id' => $this->parent['id'],
                )
            );
        return $parent->getEvent()->getArea();
    }

    /**
     * Update Parent class if this is a new SeatArea
     *
     * @param object $formObject
     */
    private function updateParentAndFlush($formObject) {
        $newId = $formObject->getId();
        if (empty($newId)) {
            $this->entityManager->persist($formObject);
            $this->entityManager->flush();
            $parent = $this->entityManager->getRepository(self::PARENTS_NAMESPACE .ucfirst($this->parent['class']))
                ->findOneBy(
                    array(
                        'id' => $this->parent['id'],
                    )
                );
            $parent->setArea($formObject);
            $this->entityManager->persist($parent);
        } else {
            $this->entityManager->persist($formObject);
        }
        $this->entityManager->flush();
    }
}
