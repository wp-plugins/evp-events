<?php

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;

use Symfony\Component\HttpFoundation\Session\Session;

class SchemaTypes extends AjaxAbstract implements AjaxInterface
{
    /**
     * Returns fields based on field type schema
     * 
     * @return array
     */
    
    public function getResult()
    {
        $actionSupplements = $this->actionSupplements;

        if ($this->session->has($actionSupplements['parent_session_key'])) {
            $parentSession = $this->session->get($actionSupplements['parent_session_key']);
        }

        $event = $this->entityManager->getRepository('EvpTicketBundle:Event');
        $eventObj = $event->find($parentSession['id']);

        $fieldSchemaRepository = $this->entityManager->getRepository('EvpTicketBundle:Form\FieldSchema');
        $fieldSchemas = $fieldSchemaRepository->getByEventAndNotRequiredForAll($eventObj, $this->scopeId);
        
        $return = array();
        $return['type'] = 'collection';
        
        $data = array();
        
        if (is_array($fieldSchemas) && count($fieldSchemas)) {
            foreach($fieldSchemas as $schema) {
                $data[$schema->getId()] = $schema->getName();
            }
        }
        
        $return['data'] = $data;

        return $return;

    }

}

