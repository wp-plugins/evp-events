<?php
/**
 * Annotation for TicketBundle Entities to display on Entity Index in admin page
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Annotation;

/**
 * Class IndexColumn
 * @Annotation
 */
class ListedColumn {

    /**
     * @var string
     */
    private $translationTag;

    /**
     * Sets the properties
     * @param array $options
     */
    public function __construct($options) {
        $this->translationTag = $options['value'];
    }

    /**
     * Gets the translation tag for property
     * @return string
     */
    public function getTranslationTag() {
        return $this->translationTag;
    }
}
