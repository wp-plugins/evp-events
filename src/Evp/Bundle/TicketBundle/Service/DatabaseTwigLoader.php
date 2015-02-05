<?php
/**
 * Loads Twig template from Database
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

/**
 * Class DatabaseTwigLoader
 */
class DatabaseTwigLoader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface {

    /**
     * @var
     */
    private $templateManager;

    /**
     * @var object
     */
    private $object = null;

    /**
     * @var string
     */
    private $type = null;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Twig_LoaderInterface
     */
    private $nativeLoader;

    /**
     * Sets requirements
     *
     * @param TwigTemplateManager $twg
     * @param \Twig_Environment $env
     * @param \Twig_LoaderInterface $nativeLoader
     */
    public function __construct(
        TwigTemplateManager $twg,
        \Twig_Environment $env,
        \Twig_LoaderInterface $nativeLoader
    ) {
        $this->templateManager = $twg;
        $this->twig = $env;
        $this->nativeLoader = $nativeLoader;
    }

    /**
     * Sets printable object
     *
     * @param object $object
     * @return self
     */
    public function setObject($object) {
        if (!empty($object)) {
            $this->object = $object;
        }
        return $this;
    }

    /**
     * Set current template type
     *
     * @param string $str
     * @return self
     */
    public function setType($str = null) {
        $this->type = $str;
        return $this;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig() {
        $chainLoader = new \Twig_Loader_Chain(array($this->nativeLoader, $this));

        $this->twig->setLoader($chainLoader);
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return string
     * @throws \Twig_Error_Loader
     */
    public function getSource($name) {
        $this->setParameters();

        if (false === $source = $this->templateManager->getTemplateSource($name)) {
            throw new \Twig_Error_Loader(sprintf('Template "%s" does not exist.', $name));
        }
        return $source;
    }

    /**
     * Sets the current parameters
     *
     * @return self
     */
    public function setParameters() {
        $this->templateManager
            ->setObject($this->object)
            ->setType($this->type);
        return $this;
    }

    /**
     * Gets found template as an Entity
     *
     * @param string $name
     * @return object
     */
    public function getTemplateEntity($name) {
        $this->setParameters();
        return $this->templateManager->getTemplate($name);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return string
     */
    public function getCacheKey($name) {
        return $name;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param \timestamp $time
     * @return bool
     */
    public function isFresh($name, $time) {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return bool
     */
    public function exists($name) {
        return true;
    }
}
