<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/WordPressBridge.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();

// most dirty some/page/ to some/page fix ever!
$_SERVER['REQUEST_URI'] = preg_replace('|/$|', '', $_SERVER['REQUEST_URI'], 1);

$request = Request::createFromGlobals();

$wordPressBridge = WordPressBridge::createFromKernel($kernel);
$wordPressBridge->handleStaticAssets($request);

if ($wordPressBridge->isAdminRoute($request)) {
    $wordPressBridge->redirectIfNotAuthorized();
}

$response = null;
try {
    $response = $kernel->handle($request, \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST, false);
} catch(NotFoundHttpException $httpException) {
    // Let wordPress handle this route
} catch(AccessDeniedHttpException $accessException) {
    echo ($accessException);
} catch(\Exception $generalException) {
    echo ($generalException);
}

$wordPressBridge->setDefaultTimezone();

// No need to continue if the route has been handled (lets not give wordPress any freedom)
if ($wordPressBridge->isRouteHandledBySymfony($response)) {
    $wordPressBridge->renderResponseInsidePartialTemplates($response);
    $kernel->terminate($request, $response);
    exit;
}
