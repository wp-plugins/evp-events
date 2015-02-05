<?php

namespace Evp\Bundle\TicketMaintenanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array $path An array of path parameters
     * @param array $query An array of query parameters
     *
     * @return Response A Response instance
     */
    public function forward($controller, array $path = array(), array $query = array())
    {
        $response = parent::forward($controller, $path, $query);
        return $this->appendNoPartials($response);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        $response = parent::redirect($url, $status);
        return $this->appendNoPartials($response);
    }

    /**
     * Tells the controller to skip partials (header / footer parts) on render
     *
     * @param Response $response
     * @return Response
     */
    protected function appendNoPartials($response)
    {
        $response->headers->add(array('no-partials' => 1));
        return $response;
    }

    /**
     * Removes caching on client-side
     *
     * @param string   $view
     * @param array    $parameters
     * @param Response $response
     *
     * @return Response
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $response = parent::render($view, $parameters, $response);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        return $response;
    }
} 
