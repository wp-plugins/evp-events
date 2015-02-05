<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WordPressBridge
 * A bridge between two worlds - Wordpress and Symfony
 */
class WordPressBridge
{
    /**
     * @var array
     */
    protected $assetTypes = array(
        'js' => 'text/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'txt' => 'text/plain',
        'json' => 'application/json',
    );

    /**
     * @var array
     */
    protected $assetsSearchPaths = array(
        '/bundles/',
        '/assets_media/',
    );

    /**
     * @var AppKernel
     */
    protected $kernel;


    /**
     * @var string
     */
    protected $requestedUri;

    /**
     * @param AppKernel $kernel
     */
    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->requestedUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    /**
     * Adds wordPress header and footer template partials if symfony handled the route
     *
     * @param Response $response
     */
    public function renderResponseInsidePartialTemplates(Response $response)
    {
        if ($this->isRouteHandledBySymfony($response)) {

            $this->preventPageNotFoundTitle();
            $this->showHeaderAndFooterIfNeeded($response);
        }
    }

    /**
     * Checks if the symfony router found the controller to handle the request
     *
     * @param Response $response
     * @return bool
     */
    public function isRouteHandledBySymfony($response)
    {
        return $response !== null && $response->getStatusCode() != '404';
    }

    /**
     * Prevents wordPress from displaying the 'Page not found' title
     */
    private function preventPageNotFoundTitle()
    {
        $wpQuery = $GLOBALS['wp_query'];
        $wpQuery->is_404 = false;
    }

    /**
     * Renders wordPress partial templates unless it's a route to an asset
     * specified by the $assetTypes variable
     *
     * @param Response $response
     */
    private function showHeaderAndFooterIfNeeded(Response $response)
    {
        $responseHasContentType = $response->headers->has('Content-Type');
        $itsNotHtml = $responseHasContentType && stripos(
                $response->headers->get('Content-Type'),
                'text/html'
            ) === false;

        $responseHasNoPartialsHeader = $response->headers->has('No-Partials');

        $assetExtensions = array_keys($this->assetTypes);
        $isAssetExtension = $this->isFileWithAssetExtensionRequested($assetExtensions);

        $headerAndFooterIgnored = ($responseHasContentType && $itsNotHtml) || $isAssetExtension || $responseHasNoPartialsHeader;

        if (!$headerAndFooterIgnored) {
            get_template_part('header');
        }

        if ($headerAndFooterIgnored && !$responseHasContentType) {
            $requestedExtension = $this->getRequestedAssetExtension();
            if (array_key_exists($requestedExtension, $this->assetTypes)) {
                $response->headers->set('Content-Type', $this->assetTypes[$requestedExtension]);
            }
        }

        $response->headers->remove('no-partials');
        $response->send();

        if (!$headerAndFooterIgnored) {
            get_template_part('footer');
        }
    }


    /**
     * WordPress uses UTC timezone and an offset
     */
    public function setDefaultTimezone()
    {
        date_default_timezone_set('UTC');
    }

    /**
     * @param AppKernel $kernel
     * @return static
     */
    public static function createFromKernel($kernel)
    {
        return new static($kernel);
    }

    /**
     * Check if the route contains the string 'admin'
     *
     * @param Request $request
     * @return bool
     */
    public function isAdminRoute(Request $request)
    {
        return stripos($request->getRequestUri(), 'admin') !== false;
    }

    /**
     * Redirect to the wp admin if the user is not an admin
     */
    public function redirectIfNotAuthorized()
    {
        if (!current_user_can('manage_options')) {
            wp_redirect(get_option('siteurl') .'/wp-admin');
            exit;
        }
    }

    public function handleStaticAssets(Request $request) {
        $assetRoute = $matches = array();
        preg_match('/\/(\w+)\//', $request->getRequestUri(), $matches);
        if (!empty($matches)) {
            $assetRoute = in_array($matches[0], $this->assetsSearchPaths);
        }

        if ($assetRoute) {
            $staticAssetRoute = plugins_url( 'web' , dirname(__FILE__ )) . $request->getRequestUri();
            wp_redirect($staticAssetRoute);
            exit;
        }
    }

    /**
     * @param array $assetExtensions
     * @return bool
     */
    private function isFileWithAssetExtensionRequested($assetExtensions)
    {
        $hasAssetExtension = false;
        foreach ($assetExtensions as $extension) {
            $requestedFileHasAssetExtension = substr_compare(
                    $this->requestedUri,
                    $extension,
                    -strlen($extension)
                ) === 0;

            if ($requestedFileHasAssetExtension) {
                $hasAssetExtension = true;
                break;
            }
        }

        return $hasAssetExtension;
    }

    /**
     * @return string
     */
    private function getRequestedAssetExtension()
    {
        $lastDotPosition = strrpos($this->requestedUri, '.');
        $requestedExtension = 'txt';

        if ($lastDotPosition !== false) {
            $requestedExtension = substr($this->requestedUri, $lastDotPosition + 1);
            return $requestedExtension;
        }
        return $requestedExtension;
    }
}
