<?php

namespace Evp\Bundle\PaymentBundle\PaymentHandler;

use Evp\Bundle\PaymentBundle\Entity as Entities;
use Evp\Bundle\TIcketBundle\Entity as TicketEntities;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface HandlerInterface
 * @package Evp\Bundle\PaymentBundle\PaymentType
 *
 * Provides methods for payment library wrappers, that need to be implemented
 * before using the tag "evp.payment_handler"
 */
interface HandlerInterface {
    /**
     * @param $user
     * @return int
     */
    public function getOrderForUser($user);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     * @return Entities\PaymentType[]
     */
    public function getPaymentTypesForUser($user);

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     * @param \Evp\Bundle\PaymentBundle\Entity\PaymentType $paymentType
     *
     * @return RedirectResponse
     */
    public function getPaymentResponseForUser($user, $paymentType);

    /**
     * @param Request $request
     * @return \Evp\Bundle\TicketBundle\Entity\Order
     */
    public function handleCallback($request);

    /**
     * Provides a response to the callback request done by a 3d party site
     * when no errors were encountered
     *
     * @return Response
     */
    public function getOkResponse();

    /**
     * Provides a response to the callback request done by a 3d party site
     * when some errors were encountered
     *
     * @return Response
     */
    public function getErrorResponse();


    /**
     * @param string $name
     */
    public function setName($name);
} 
