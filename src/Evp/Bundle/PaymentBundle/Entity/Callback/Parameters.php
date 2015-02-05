<?php

namespace Evp\Bundle\PaymentBundle\Entity\Callback;

use Symfony\Component\HttpFoundation\Request;

/**
 * Parameters
 */
class Parameters
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Set request
     *
     * @param Request $request
     * @return Parameters
     */
    public function setRequest($request)
    {
        $this->request = $request;
    
        return $this;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
