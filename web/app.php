<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

umask(0000);

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';
require_once __DIR__.'/../app/WordPressBridge.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

if (extension_loaded('apc') && ini_get('apc.enabled')) {
    $kernel = new AppCache($kernel);
}

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
    $response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
} catch(NotFoundHttpException $httpException) {
    // Let wordPress handle this route
} catch(\Exception $generalException) {
    $container = null;
    if ($kernel instanceof AppCache) {
        $container = $kernel->getKernel()->getContainer();
    } else {
        $container = $kernel->getContainer();
    }

    print '<div>Ticket plugin could not be loaded. See logs for more details</div>';

    if ($container !== null) {
        $container->get('logger')->error('Ticket plugin could not be loaded.', array('exception' => $generalException->getMessage()));
    }
}

$wordPressBridge->setDefaultTimezone();

// No need to continue if the route has been handled (lets not give wordPress any freedom)
if ($wordPressBridge->isRouteHandledBySymfony($response)) {
    $wordPressBridge->renderResponseInsidePartialTemplates($response);
    $kernel->terminate($request, $response);
    exit;
}
