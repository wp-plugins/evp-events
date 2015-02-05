<?php
if (!defined('WPE_PLUGIN_DIR')) {
    define('WPE_PLUGIN_DIR', '/home/valentas/Projects/wordpress/wp-content/plugins/lib-wordpress-tickets-plugin');
}

// Any way to access the EntityManager from  your application
$em = Evp\Service\EntityManagerInitializer::newInstance()->getEntityManager();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));
