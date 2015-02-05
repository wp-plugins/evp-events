<?php

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

class Events extends AjaxAbstract implements AjaxInterface
{
    /**
     * @var String
     */
    private $entityClass = 'Evp\Bundle\TicketBundle\Entity\Event';

    /**
     * Hold keys for the entity object methods
     *
     * @var array
     */
    private $functionMap = array(
        'ticketTypes' => 'getTicketTypes',
        'id' => 'getId',
        'name' => 'getName',
    );

    /**
     * Response for ajax calls
     *
     * $methodToCall = 'getName';  // simple property, returns string
     * $methodToCall = 'getTicketTypes'; // one to many // returns collection
     * $methodToCall = 'getEventType'; // many to one // returns object

     * $return['type'] = 'collection' will indicate that it will populate a drop down field on client side.
     *
     * @return array
     */
    public function getResult()
    {
        $methodToCall = $this->functionMap[$this->target];
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entityObj = $repository->find($this->scopeId);

        $return = array();        
        $data = array();
        
        $response = call_user_func(array($entityObj, $methodToCall));

        if (gettype($response) == 'object') {
            if ($response instanceof \Doctrine\Common\Collections\Collection) {
                foreach ($response as $resp) {
                    $data [$resp->getId()] = $resp->getName();
                }
            } else {
                $data[$response->getId()] = $response->getName();
            }
        } else {
            $data[] = $response;
        }

        $return['type'] = 'collection';
        $return['data'] = $data;

        return $return;
    }

}

