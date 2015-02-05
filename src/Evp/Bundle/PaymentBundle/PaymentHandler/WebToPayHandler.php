<?php

namespace Evp\Bundle\PaymentBundle\PaymentHandler;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\PaymentBundle\Entity as Entities;
use Evp\Bundle\PaymentBundle\PaymentHandler\Exception\OrderIntegrityException;
use Evp\Bundle\PaymentBundle\PaymentHandler\Exception\OrderStatusException;
use Evp\Bundle\PaymentBundle\PaymentHandler\Exception\WebToPayHandlerException;
use Evp\Bundle\TicketBundle\Entity as TicketEntities;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \WebToPay_Factory;

/**
 * Class WebToPayHandler
 * @package Evp\Bundle\PaymentBundle\PaymentHandler
 */
class WebToPayHandler extends HandlerAbstract
{

    const CALLBACK_STATUS_PAYMENT_CONFIRMED = 1;

    /**
     * @var WebToPay_Factory
     */
    private $webToPayFactory;

    private $testMode;

    /**
     * @param Router $router
     * @param UserSession $userSession
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param WebToPay_Factory $webToPayFactory
     * @param bool $testMode
     */
    function __construct(
        Router $router,
        UserSession $userSession,
        Logger $logger,
        EntityManager $entityManager,
        WebToPay_Factory $webToPayFactory,
        $testMode
    )
    {
        parent::__construct(
            $router,
            $userSession,
            $logger,
            $entityManager
        );

        $this->webToPayFactory = $webToPayFactory;
        $this->testMode = $testMode;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentTypesForUser($user)
    {
        $this->getLogger()->debug(__METHOD__);

        $order = $this->getOrderForUser($user);
        $event = $order->getEvent();

        $this->getLogger()->debug(
            'Got event and order',
            array(
                'event' => $event,
                'order' => $order
            )
        );

        $locale = $this->userSession->getUserLocale();
        if ($locale === null) {
            $locale = $event->getDefaultLocale();
        }

        $paymentMethods = $this->webToPayFactory
            ->getPaymentMethodListProvider()
            ->getPaymentMethodList($event->getCurrency())
            ->setDefaultLanguage($locale);

        if ($order->getOrderPrice() === null) {
            throw new Exception('Order price was not set on the last step');
        }

        $paymentMethodsForCountry = $paymentMethods->getCountry($event->getCountryCode());
        if ($paymentMethodsForCountry === null) {
            $this->getLogger()->error(
                'Could not retrieve payment methods for the event',
                array(
                    'event' => $event,
                )
            );

            return array();
        }

        $paymentGroups = $paymentMethodsForCountry->filterForAmount(
                $order->getOrderPrice() * 100,
                $event->getCurrency()
            )
            ->getGroups();

        return $this->mapGroupsToPaymentTypes($paymentGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentResponseForUser($user, $paymentType)
    {

        $request = $this->webToPayFactory
            ->getRequestBuilder()
            ->buildRequest(
                array(
                    'p_email' => $user->getEmail(),
                    'orderid' => $user->getOrder()->getId(),
                    'amount' => $user->getOrder()->getOrderPrice() * 100,
                    'currency' => $user->getOrder()->getEvent()->getCurrency(),
                    'country'  => $user->getOrder()->getEvent()->getCountryCode(),
                    'accepturl' => $this->getAcceptUrlForUser($user),
                    'cancelurl' => $this->getCancelUrl(),
                    'callbackurl' => $this->getCallbackUrl(),
                    'payment' => $paymentType->getName(),
                    'test' => $this->testMode,
                )
            );

        $url = \WebToPay::PAYSERA_PAY_URL . '?' . http_build_query($request);
        $status = 302;

        return new RedirectResponse($url, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback($request)
    {
        $orderRepository = $this->getEntityManager()->getRepository('EvpTicketBundle:Order');

        $requestParameters = $request->request->count() !== 0 ? $request->request->all() : $request->query->all();

        $callback = Entities\PaymentCallback::create()
            ->setHandlerName($this->getName())
            ->setRawRequestData($requestParameters);

        $this->getEntityManager()->persist($callback);

        try {
            $callbackValidator = $this->webToPayFactory->getCallbackValidator();
            $parsedRequestData = $callbackValidator->validateAndParseData($requestParameters);

            $callback->setParsedRequestData($parsedRequestData);
            $isPaymentSuccessful = (int)$parsedRequestData['status'] === self::CALLBACK_STATUS_PAYMENT_CONFIRMED;

            if ($isPaymentSuccessful) {
                $order = $orderRepository->find($parsedRequestData['orderid']);
                $callback->setOrder($order);
                $this->validateMoneyReceived($order, $parsedRequestData);
                $this->getEntityManager()->flush($callback);

                return $order;
            }

             throw new OrderStatusException('Unexpected payment status');

        } catch (WebToPayHandlerException $e) {
            $this->getLogger()->error('Could not handle callback, error occurred', array($e));
            $callback->setErrorMessage($e->getMessage());
            $this->getEntityManager()->flush($callback);

        } catch (\Exception $e) {
            $this->getLogger()->error('Could not handle callback, database error occurred', array($e));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOkResponse() {
        return new Response('OK', 200,
            array(
                'content-type' => 'text/html',
                'no-partials' => 1
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorResponse() {
        return new Response('Error', 200,
            array(
                'content-type' => 'text/html',
                'no-partials' => 1
            )
        );
    }

    /**
     * @param \WebToPay_PaymentMethodGroup[] $paymentGroups
     * @return array
     */
    private function mapGroupsToPaymentTypes($paymentGroups)
    {
        $paymentTypes = array();

        foreach ($paymentGroups as $group) {
            foreach ($group->getPaymentMethods() as $paymentMethod) {
                $paymentTypes[$paymentMethod->getKey()] = Entities\PaymentType::create()
                    ->setName($paymentMethod->getKey())
                    ->setTitle($paymentMethod->getTitle())
                    ->setLogoUrl($paymentMethod->getLogoUrl())
                    ->setHandlerClass($this->getName());
            }
        }
        return $paymentTypes;
    }

    /**
     * @return string
     */
    private function getCallbackUrl()
    {
        return $this->getRouter()->generate(
            'handle_callback',
            array(
                'paymentHandlerName' => $this->getName()
            ),
            true
        );
    }

    /**
     * @param TicketEntities\Order $order
     * @param array $parsedRequestData
     * @throws OrderIntegrityException
     */
    private function validateMoneyReceived($order, $parsedRequestData)
    {
        $requestAmount = number_format($parsedRequestData['amount'] / 100, 2);
        $requestCurrency = $parsedRequestData['currency'];

        $isAmountDifferent = number_format($order->getOrderPrice(), 2) !== $requestAmount;
        $isCurrencyDifferent = $order->getEvent()->getCurrency() !== $requestCurrency;


        if ($isAmountDifferent || $isCurrencyDifferent) {
            throw new OrderIntegrityException('Order amount or currency mismatch');
        }
    }

    /**
     * @param $user
     * @return string
     */
    private function getAcceptUrlForUser($user)
    {
        return $this->getRouter()->generate(
            'payment_completed',
            array(
                'orderToken' => $user->getOrder()->getToken()
            ),
            true
        );
    }

    /**
     * @return string
     */
    private function getCancelUrl()
    {
        return $this->getRouter()->generate('payment_cancelled', array(), true);
    }
} 
