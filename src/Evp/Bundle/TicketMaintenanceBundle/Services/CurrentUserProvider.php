<?php
/**
 * Provides current user in top-level system (wordpress, etc.)
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketMaintenanceBundle\Services;

/**
 * Class CurrentUserProvider
 * @package Evp\Bundle\TicketAdminBundle\Service
 */
class CurrentUserProvider
{
    /**
     * @var mixed
     */
    private $user;

    /**
     * Constructs class
     */
    public function __construct()
    {
        $this->user = wp_get_current_user();
    }

    /**
     * @return array
     */
    public function getCurrentUserData()
    {
        return array(
            'username' => $this->user->user_login,
            'email' => $this->user->user_email,
        );
    }
}
