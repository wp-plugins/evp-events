<?php
namespace Evp\Bundle\TicketBundle\Service;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\EventDispatcher\UserSessionEvents;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession as Events;
/**
 * Class UserSession
 * @package Evp\Bundle\TicketBundle\Service
 *
 * Creates a user entity and attaches it to the session
 */
class UserSession extends ManagerAbstract {

    /**
     * @var Session
     */
    private $session;
    /**
     * @var string
     */
    private $indexName;
    /**
     * @var EventDispatcher $dispatcher
     */
    private $dispatcher;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param EntityManager      $em
     * @param \Monolog\Logger    $logger
     * @param Session            $session
     * @param                    $indexName
     * @param EventDispatcher    $dispatcher
     * @param ContainerInterface $container
     */
    function __construct(
        EntityManager $em,
        Logger $logger,
        Session $session,
        $indexName,
        EventDispatcher $dispatcher,
        ContainerInterface $container
    )
    {
        parent::__construct($em, $logger);
        $this->session = $session;
        $this->indexName = $indexName;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }


    /**
     * @return string|null
     */
    public function getUserLocale()
    {
        if ($this->container->isScopeActive('request')) {
            $request = $this->container->get('request');
            return $request->getLocale();
        }

        return null;
    }

    /**
     * Gets a user with an order attached.
     *
     * @return User
     */
    public function getUserForThisSession() {
        $userForThisSession = null;

        if ($this->isUserSavedInSession()) {
            $user = $this->getUserFromSession();
            $order = $user->getOrder();

            $orderInProgressStatuses = array(Order::STATUS_IN_PROGRESS, Order::STATUS_AWAITING_PAYMENT);
            $orderNotDone = in_array($order->getStatus(), $orderInProgressStatuses);

            if(isset($order) && $orderNotDone) {
                $userForThisSession = $user;
            }
        }

        return $userForThisSession;
    }


    /**
     * Creates a user and attaches it to the session
     *
     * @return User
     */
    public function createNewUserForThisEvent()
    {
        $user = $this->persistNewUser();
        $this->saveUserToSession($user);

        $this->dispatcher->dispatch(
            UserSessionEvents::SESSION_CREATED,
            new Events\Created($user)
        );
        return $user;
    }

    /**
     *  Destroys the user session
     */
    public function destroyCurrentSession($dispatchEvents = true) {
        $user = $this->getUserForThisSession();
        if (!$user) {
            return null;
        }

        $this->entityManager->flush($user);

        $this->session->remove($this->indexName);

        if ($dispatchEvents) {
            $this->dispatcher->dispatch(
                UserSessionEvents::SESSION_DESTROYED,
                new Events\Destroyed($user)
            );
        }

        return $user;
    }

    public function isCurrentUserValid()
    {
        return $this->getUserForThisSession() !== null;
    }

    /**
     * @return bool
     */
    protected function isUserSavedInSession()
    {
        return $this->session->has($this->indexName);
    }

    /**
     * @return User
     */
    protected function getUserFromSession()
    {
        $userId = $this->session->get($this->indexName);
        $user = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\User')
            ->find($userId);
        return $user;
    }

    /**
     * @return User
     */
    protected function persistNewUser()
    {
        $currentDate = new \DateTime("now");

        $user = User::create()
            ->setDateCreated($currentDate);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }


    /**
     * @param User $user
     */
    protected function saveUserToSession(User $user)
    {
        $this->session->set($this->indexName, $user->getId());
    }
} 
