<?php
/**
 * Listens for REMOVE events & sets pragma values in Sqlite
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketMaintenanceBundle\Listener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Event\ConnectionEventArgs;

/**
 * Class SqliteForeignKeysEnable
 */
class SqliteForeignKeysEnable implements EventSubscriber
{
    const DRIVER_NAME = 'pdo_sqlite';

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postConnect',
        );
    }

    /**
     * @param ConnectionEventArgs $args
     */
    public function postConnect(ConnectionEventArgs $args)
    {
        $conn = $args->getConnection();
        $driver = $args->getDriver()->getName();

        if ($driver == self::DRIVER_NAME) {
            $conn->query("PRAGMA foreign_keys = ON");
        }
    }
} 
