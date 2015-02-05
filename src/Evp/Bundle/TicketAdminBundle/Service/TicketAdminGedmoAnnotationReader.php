<?php
/**
 * Gedmo Annotation reader for Ajax returning translatable columns.
 * @author Khalid Hameed <k.hameed@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service;

use Doctrine\Common\Annotations\Reader;

/**
 * Class TicketAdminGedmoAnnotationReader
 */
class TicketAdminGedmoAnnotationReader {

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $annotationClass = 'Gedmo\\Mapping\\Annotation\\Translatable';

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
            $annotation = $this->reader->getPropertyAnnotation($property, $this->annotationClass);
            if ($annotation !== null) {
                $columns[] = $property->getName();
            }
        }
        return $columns;
    }
}
