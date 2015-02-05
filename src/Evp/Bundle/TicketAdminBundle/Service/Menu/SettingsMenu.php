<?php
/**
 * SettingsMenu for managing System settings
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Evp\Bundle\TicketAdminBundle\Form\ParametersFormType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Class SettingsMenu
 */
class SettingsMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Discount';

    /**
     * @var string
     */
    protected $menuClass = 'Settings';

    /**
     * @var string
     */
    protected $menuTransName = 'settings';

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketAdminBundle\Form\ParametersFormType';

    /**
     * @var array
     */
    private $viewConfig;

    /**
     * Sets current Action name
     *
     * @param string $action
     */
    public function setCurrentAction($action) {
        $this->currentAction = $this->actions[$action];
        $parameters = $this->loadParametersFromYaml();
        $testMailUrl = $this->router->generate('admin_test_mail_settings', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $form = new ParametersFormType($parameters, $this->viewConfig, $testMailUrl);

        $this->currentAction->setParameters(
            array(
                'formObj' => $parameters,
                'form' => $form,
                'request' => $this->request,
                'rootDir' => $this->rootDir,
            )
        );
    }

    /**
     * Kernel root dir
     *
     * @param string $dir
     */
    public function setRootDir($dir) {
        $this->rootDir = $dir;
    }

    /**
     * @param array $config
     */
    public function setViewConfiguration($config)
    {
        $this->viewConfig = $config;
    }

    /**
     * Gets the sub-menus by [action] = [translation.tag] pattern
     *
     * @return array
     */
    public function getSpecificMenuItems() {
        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => array(),
        );
    }

    /**
     * Load the parameters from the parameters.yml file
     *
     * @return array
     */
    private function loadParametersFromYaml()
    {
        $parameterPath = $this->getParametersFilePath();
        $content = file_get_contents($parameterPath);

        $yaml = new Parser();
        $parsedContent = $yaml->parse($content, false, true);
        $parameters = $parsedContent['parameters'];
        return $parameters;
    }


    /**
     * @return string
     */
    private function getParametersFilePath()
    {
        return sprintf('%s/config/parameters.yml', $this->rootDir);
    }
}
