<?php
/**
 * Annotation for TicketBundle Entities to enable filtering by column
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Annotation;

/**
 * Class FilteredColumn
 * @Annotation
 */
class FilteredColumn {
    /**
     * @var string
     */
    private $relatedField = null;

    /**
     * Sets the properties
     * @param array $options
     */
    public function __construct($options) {
        if (array_key_exists('value', $options)) {
            $this->relatedField = $options['value'];
        }
    }

    /**
     * Gets the translation tag for property
     * @return string
     */
    public function getRelatedField() {
        return $this->relatedField;
    }
}
