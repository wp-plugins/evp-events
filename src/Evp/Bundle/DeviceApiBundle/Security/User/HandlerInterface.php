<?php
/**
 * Common interface for authentication handlers
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Security\User;

/**
 * Interface HandlerInterface
 * @package Evp\Bundle\DeviceApiBundle\Security\Authentication\Provider
 */
interface HandlerInterface {

    /**
     * Validates given string/object in this Handler
     *
     * @param mixed
     * @return mixed
     */
    function validate($param);

    /**
     * Gets the validated Entity
     *
     * @return object
     */
    function getEntity();
}
