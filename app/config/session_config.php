<?php
// Checks if session is already started by some custom service and initiates Symfony session bridge
/** @var $container  */
$started = false;

$id = session_id();
if (!empty($id)) {
    $started = true;
}
if (!empty($_COOKIE["PHPSESSID"])) {
    $started = true;
}

if ($started) {
    if (php_sapi_name() === 'cli') {
        require_once __DIR__ . '/../../../../../wp-config.php';
        $container->setParameter('framework.session.storage_id', 'session.storage.php_bridge');
    } else {
        $container->setParameter('framework.session.storage_id', 'session.storage.php_bridge');
    }
}
