<?php

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;

use Evp\Bundle\TicketAdminBundle\Service\TicketAdminGedmoAnnotationReader;

/**
 * Class RefreshTemplate
 */
class RefreshTemplate extends AjaxAbstract implements AjaxInterface
{
    /**
     * @var String
     */
    private $entityClass = 'Evp\Bundle\TicketBundle\Entity\Template';

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\TicketAdminGedmoAnnotationReader
     */
    private $gedmoAnnotationReader;

    /**
     * Sets Annotation reader
     * @param TicketAdminGedmoAnnotationReader $reader
     */
    public function setAnnotationReader(TicketAdminGedmoAnnotationReader $reader) {
        $this->gedmoAnnotationReader = $reader;
    }

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
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entityObj = $repository->findOneBy(
            array(
                'id' =>$this->scopeId
            )
        );
        $entityObj->setLocale($this->target);
        $this->entityManager->refresh($entityObj);

        $translatableColumns = $this->gedmoAnnotationReader->getListedColumns(new $this->entityClass);

        $translatedColumns = array();
        foreach($translatableColumns as $col) {
            $method= 'get' . ucfirst($col);
            $translatedColumns[$col] = stripslashes(call_user_func(array($entityObj, $method)));
        }
        return $translatedColumns;
    }

}

