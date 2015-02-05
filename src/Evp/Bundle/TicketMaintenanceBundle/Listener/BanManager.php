<?php
/**
 * Listens for Request Event and bans if matches any rule
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketMaintenanceBundle\Listener;

use Evp\Bundle\TicketMaintenanceBundle\Services\CurrentUserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Router;

/**
 * Class BanManager
 */
class BanManager
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CurrentUserProvider
     */
    private $userProvider;

    /**
     * @var array
     */
    private $bannedRoutes;

    /**
     * @var array
     */
    private $bannedIps;

    /**
     * @var array
     */
    private $bannedUserAgents;

    /**
     * @param Router                                                           $router
     * @param \Psr\Log\LoggerInterface                                         $logger
     * @param \Evp\Bundle\TicketMaintenanceBundle\Services\CurrentUserProvider $userProvider
     * @param array                                                            $ips
     * @param array                                                            $userAgents
     * @param array                                                            $routes
     */
    public function __construct(
        Router $router,
        LoggerInterface $logger,
        CurrentUserProvider $userProvider,
        $ips,
        $userAgents,
        $routes
    ) {
        $this->router = $router;
        $this->logger = $logger;
        $this->userProvider = $userProvider;
        $this->bannedIps = $ips;
        $this->bannedUserAgents = $userAgents;
        $this->bannedRoutes = $routes;
    }

    /**
     * Drops current request if matched by any rule
     *
     * @param GetResponseEvent $event
     */
    public function banByRules(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        $request = $event->getRequest();

        $this->logger->debug(
            'Got new Request from IP ' .$request->getClientIp().' with Headers',
            $request->headers->all()
        );

        if (empty($this->bannedRoutes)) {
            $this->processRules($request);
        } else {
            $routeParams = $this->router->match($request->getPathInfo());
            $route = $routeParams['_route'];
            if (in_array($route, $this->bannedRoutes)) {
                $this->processRules($request);
            }
        }
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    private function processRules(Request $request)
    {
        if (in_array($request->getClientIp(), $this->bannedIps)) {
            $this->logger->debug('Matched rule for IP ban', array($request->getClientIp()));
            throw new \Exception('This IP is blocked');
        }
        foreach ($this->bannedUserAgents as $agent) {
            if (preg_match('#' .$agent .'#', $request->headers->get('user-agent'))) {
                $this->logger->debug('Matched rule for User-Agent ban', array($request->headers->get('user-agent')));
                throw new \Exception('This User-Agent is blocked');
            }
        }
    }
} 
