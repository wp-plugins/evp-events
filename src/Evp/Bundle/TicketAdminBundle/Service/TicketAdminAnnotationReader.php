<?php
/**
 * Annotation reader from TicketBundle Entities for TicketAdminBundle Index Action
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service;

use Doctrine\Common\Annotations\Reader;

/**
 * Class TicketAdminAnnotationReader
 */
class TicketAdminAnnotationReader {

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $listedColumnsAnnotation = 'Evp\\Bundle\\TicketAdminBundle\\Annotation\\ListedColumn';

    /**
     * @var string
     */
    private $filteredColumnsAnnotation = 'Evp\\Bundle\\TicketAdminBundle\\Annotation\\FilteredColumn';

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
            $annotation = $this->reader->getPropertyAnnotation($property, $this->listedColumnsAnnotation);
            if ($annotation !== null) {
                $columns[$property->getName()] = $annotation->getTranslationTag();
            }
        }
        return $columns;
    }

    /**
     * Reads the Annotation and prepares the array.
     * @param object $entity
     * @return array
     */
    public function getFilterableColumns($entity) {
        $columns = array();

        $reflectionObj = new \ReflectionObject($entity);
        foreach ($reflectionObj->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, $this->filteredColumnsAnnotation);
            if ($annotation !== null) {
                if (!$annotation->getRelatedField()) {
                    $columns[] = $property->getName();
                } else {
                    $columns[$property->getName()] = $annotation->getRelatedField();
                }
            }
        }
        return $columns;
    }
}
