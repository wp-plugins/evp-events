<?php
// This part configures symfony to use the wordpress constants when connecting to db
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../../../../wp-config.php';
}
$parsedPath = parse_url(get_option('siteurl'), PHP_URL_PATH);
$sitePath = empty($parsedPath) ? '' : trim($parsedPath, '/');

/** @var $container  */
$container->setParameter('database_host', DB_HOST);
$container->setParameter('database_port', 'null');
$container->setParameter('database_name', DB_NAME);
$container->setParameter('database_user', DB_USER);
$container->setParameter('database_password', DB_PASSWORD);
$container->setParameter('utc_offset', get_option('gmt_offset'));
$container->setParameter('server_hostname', get_option('siteurl'));
$container->setParameter('site_path', $sitePath);
$container->setParameter('plugin_url_path', 'evp-tickets');
