<?php
/**
 * Interface for Cascade actions on Entities
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador;

/**
 * Interface CascadorInterface
 */
interface CascadorInterface {

    /**
     * Cascades the specific relations
     *
     * @param object $obj
     */
    function cascade($obj);
}
