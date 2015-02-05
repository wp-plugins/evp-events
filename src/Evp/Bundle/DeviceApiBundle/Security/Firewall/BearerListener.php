<?php
/**
 * Bearer authentication listener
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Security\Firewall;

use Evp\Bundle\DeviceApiBundle\Security\Authentication\Token\BearerExaminerToken;
use Evp\Bundle\DeviceApiBundle\Security\Authentication\Token\BearerTokenInterface;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Class BearerListener
 * @package Evp\Bundle\DeviceApiBundle\Security\Firewall
 */
class BearerListener implements ListenerInterface {

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var string
     */
    private $bearerRegex;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * Sets requirements
     *
     * @param SecurityContextInterface $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     * @param string $regex
     * @param \Monolog\Logger $log
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        $regex,
        Logger $log
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->bearerRegex = $regex;
        $this->logger = $log;
    }

    /**
     * {@inheritdoc}
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event) {
        $request = $event->getRequest();
        $this->fixAuthHeader($request->headers);
        $this->logger->addDebug('authorization approach logged', $request->headers->all());

        if (
            !$request->headers->has('Authorization')
            || 1 !== preg_match($this->bearerRegex, $request->headers->get('Authorization'), $matches)
        ) {
            $this->logger->addDebug('no Authorization header found in request', $request->headers->all());
            return;
        }
        $this->logger->addDebug('Authorization header found', array($request->headers->get('Authorization')));
        $token = new BearerExaminerToken;
        $token->setToken($matches[1]);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $e) {
            $this->logger->addDebug('authentication failed for token', array($token));
            if ($token instanceof BearerTokenInterface) {
                $this->securityContext->setToken(null);
            }
            $response = new Response;
            $response->setStatusCode(403);
            $event->setResponse($response);
        }
        $response = new Response;
        $response->setStatusCode(403);
        $event->setResponse($response);
    }

    /**
     * Fixes Apache Authorization Header issue
     *
     * @param HeaderBag $headers
     */
    private function fixAuthHeader(HeaderBag $headers)
    {
        if (!$headers->has('Authorization') && function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            if (isset($apacheHeaders['Authorization'])) {
                $headers->set('Authorization', $apacheHeaders['Authorization']);
            }
        }
    }
}