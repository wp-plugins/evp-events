<?php

namespace Evp\Bundle\TicketMaintenanceBundle\Services;

use Doctrine\Common\Annotations\Reader;

/**
 * Provides methods for reading fields that need to become unique tokens
 *
 * Class UniqueTokenAnnotationReader
 * @package Evp\Bundle\TicketMaintenanceBundle\Services
 */
class UniqueTokenAnnotationReader {

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $uniqueTokenAnnotation = 'Evp\\Bundle\\TicketMaintenanceBundle\\Annotation\\UniqueToken';

    /**
     * Sets the Annotation reader
     * @param \Doctrine\Common\Annotations\Reader $reader
     */
    public function __construct(Reader $reader) {
        $this->reader = $reader;
    }

    /**
     * Reads the Annotation and prepares the array.
     * @param object $entity
     * @return array
     */
    public function getListedColumns($entity) {
        $columns = array();

        $reflectionObj = new \ReflectionObject($entity);
        foreach ($reflectionObj->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, $this->uniqueTokenAnnotation);
            if ($annotation !== null) {
                $columns[] = $property->getName();
            }
        }
        return $columns;
    }
} 