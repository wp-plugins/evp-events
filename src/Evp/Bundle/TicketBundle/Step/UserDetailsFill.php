<?php
/**
 * UserDetailsFill step from multi-step forms
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Form\FieldRecord;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Step\Forms\DynamicCollectionType;
use Evp\Bundle\TicketBundle\Step\Forms\UserDetailsFillForm;
use Evp\Bundle\TicketBundle\Entity\Dynamic as Dynamic;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserDetailsSelection
 * @package Evp\Bundle\TicketBundle\Step
 *
 * Generates a step, where the user has to fill his details
 */
class UserDetailsFill extends StepAbstract implements StepInterface {
    const MAIN_EMAIL_NAME = 'email';
    const MAIN_EMAIL_LABEL = 'label.main_email';
    const SCHEMA_GENERAL = 'general';
    const SCHEMA_COMMON = 'common';
    const SCHEMA_COMMON_FILLED = 'common_filled';
    const SCHEMA_GENERAL_FILLED = 'general_filled';

    const TICKET_ID_HIDDEN_NAME = '_ticket';
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    const STEP_USER_DETAILS_FILL = 'user_details_fill';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    protected $event;

    /**
     * @var string
     */
    protected $stepTemplatePath;

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    protected $form;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    protected $user;

    /**
     * @var array
     */
    protected $formBody = array();

    /**
     * @var \stdClass
     */
    protected $formFields;

    /**
     * @var string
     */
    protected $schema;

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params) {
        $this->stepTemplatePath = $params['template'];
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isStepCompleted()
    {
        return !$this->hasUnfilledFields();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_USER_DETAILS_FILL;
    }

    /**
     * Validates step data
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function validate(Request $request)
    {
        $this->logger->debug('Validating Request with Parameters', $request->request->all());
        $form = $this->getForm();
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $errors = array();
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            if (empty($errors)) {
                $errors[] = '';
            }

            $this->logger->debug('Form not valid', $errors);
            return array_unique($errors);
        }

        return null;
    }

    /**
     * @return array|Response
     */
    public function render() {
        $this->formBody['form'] = $this->getForm()->createView();

        return $this->formBody;
    }

    /**
     * Saves step data
     */
    public function save(Request $request)
    {
        if (!$this->hasUnfilledFields()) {
            return true;
        }
        $form = $this->getForm();
        $form->handleRequest($request);

        /** @var Dynamic\Collection $collection */
        $collection = $form->getData();

        $this->saveGlobalSettings($collection);
        $this->saveCommonSettings($collection);


        if (!$this->hasUnfilledFields()) {
            return true;
        }

        return false;
    }

    /**
     * Checks for remaining unfilled fields
     *
     * @return bool
     */
    public function hasUnfilledFields() {
        $unfilledFields = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldSchema')
            ->getUnfilledFieldsByEventAndUser($this->event, $this->user);
        $this->logger->debug('User unfilled fields count is ' .count($unfilledFields));

        $unfilledTickets = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->getUnfilledTicketsByEventAndUser($this->event, $this->user);
        $this->logger->debug('User unfilled tickets count is ' .count($unfilledTickets));

        $mainEmail = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\User')
            ->getUserWithEmail($this->user);
        $this->logger->debug('User has email address ', array(!empty($mainEmail)));

        if (
            empty($unfilledTickets)
            && empty($unfilledFields)
            && !empty($mainEmail)
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options) {
        $this->event = $options['event'];
        $this->user = $options['user'];
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate() {
        return $this->stepTemplatePath;
    }

    /**
     * Tries to cast unrecognised format to string
     * @param $value
     * @return string
     */
    public function castFormat($value) {
        if ($value instanceof \DateTime) {
            $value = $value->format(self::DEFAULT_DATETIME_FORMAT);
        }
        return $value;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function getForm()
    {
        $schemasGlobal = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldSchema')
            ->getUnfilledFieldsByEventAndUser($this->event, $this->user);
        $this->logger->debug('Global unfilled fields count', array(count($schemasGlobal)));

        $schemasCommon = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldSchema')
            ->getByEventTypeAllOrdered($this->event->getEventType(), 1);
        $this->logger->debug('Common fields count', array(count($schemasCommon)));

        $collection = new Dynamic\Collection();
        $collection->addGlobal($schemasGlobal);

        $tickets = $this->user->getTickets();

        foreach ($tickets as $ticket) {
            $ticketNumberField = array(
                'name' => '_ticket',
                'value' => $ticket->getId()
            );

            $schemasCommonCopy = $schemasCommon;
            $schemasCommonCopy[] = $ticketNumberField;

            $collection->addCommon($schemasCommonCopy);
        }

        $schemasCommon[] = array(
            'name' => '_ticket'
        );

        $formType = new DynamicCollectionType(
            $schemasGlobal,
            $schemasCommon
        );

        $collection->setTickets($tickets);

        $form = $this->formFactory->createBuilder($formType, $collection)->getForm();
        return $form;
    }

    /**
     * Traverses the dynamic entity in order to find the hidden ticket id
     *
     * @param $entity
     * @return array
     */
    private function getTicketIdFromEntity($entity)
    {
        $ticketId = null;
        foreach ($entity as $fieldName => $fieldValue) {
            if ($fieldName === self::TICKET_ID_HIDDEN_NAME) {
                $ticketId = $fieldValue;
                break;
            }
        }
        return $ticketId;
    }

    /**
     * @param $ticket
     * @throws \Exception
     */
    private function throwErrorOnInvalidUser($ticket)
    {
        if ($ticket->getUser()->getId() !== $this->user->getId()) {
            throw new \Exception('User id mismatch during order record update');
        }
    }

    /**
     * @param $collection
     * @return array
     */
    private function saveGlobalSettings($collection)
    {
        foreach ($collection->getGlobalDetails() as $entity) {
            foreach ($entity as $fieldName => $fieldValue) {

                switch ($fieldName) {
                    case self::MAIN_EMAIL_NAME:
                        $this->user->setEmail($fieldValue);
                        $this->entityManager->persist($this->user);
                        $this->entityManager->flush();
                        continue;
                }

                $fieldSchema = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldSchema')
                    ->findOneBy(array('name' => $fieldName));

                $record = new FieldRecord();
                $record->setEvent($this->event);
                $record->setUser($this->user);
                $record->setFieldSchema($fieldSchema);
                $record->setValue($fieldValue);
                $this->entityManager->persist($record);
                $this->entityManager->flush();
            }

        }
    }

    /**
     * @param $collection
     */
    private function saveCommonSettings($collection)
    {
        foreach ($collection->getCommonDetails() as $entity) {
            $ticket = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
                ->findOneBy(
                    array(
                        'id' => $this->getTicketIdFromEntity($entity)
                    )
                );

            $this->throwErrorOnInvalidUser($ticket);

            foreach ($entity as $fieldName => $fieldValue) {
                switch ($fieldName) {
                    case self::TICKET_ID_HIDDEN_NAME:
                        continue 2;
                }
                $fieldSchema = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldSchema')
                    ->findOneBy(array('name' => $fieldName));

                $record = new FieldRecord();
                $record->setEvent($this->event);
                $record->setUser($this->user);
                $record->setFieldSchema($fieldSchema);
                $record->setValue($fieldValue);
                $record->setTicket($ticket);
                $this->entityManager->persist($record);
                $this->entityManager->flush();
            }
        }
    }
}
