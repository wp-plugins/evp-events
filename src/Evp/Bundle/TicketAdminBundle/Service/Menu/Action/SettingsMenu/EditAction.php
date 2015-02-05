<?php
/**
 * EditAction for Settings menu
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\SettingsMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Evp\Bundle\TicketMaintenanceBundle\Services\SystemEnvironment;
use Symfony\Component\Yaml\Dumper;

/**
 * Class EditAction
 */
class EditAction extends ActionAbstract implements ActionInterface {

    const DUMP_LEVEL = 2;

    /**
     * @var string
     */
    protected $actionName = 'edit';

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var object
     */
    protected $formObj;

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var SystemEnvironment
     */
    private $environmentManager;

    /**
     * Sets filters for Action
     *
     * @param array $filters
     * @return self
     */
    public function setFilters($filters) {
    }

    /**
     * Gets the Result gy Entity & Filters
     *
     * @return array
     */
    public function getResult() {
    }

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        $this->rootDir = $params['rootDir'];
        $this->form = $params['form'];
        $this->request = $params['request'];
        $this->formObj = $params['formObj'];
        return $this;
    }

    /**
     * Sets the System Environment manager
     *
     * @param SystemEnvironment $env
     */
    public function setEnvironmentManager(SystemEnvironment $env) {
        $this->environmentManager = $env;
    }

    /**
     * Returns the Response type
     *
     * @return string
     */
    public function getResponseType() {
        $this->submitForm(false);
        return $this->responseType;
    }

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName() {
        if ($this->responseType == self::RESPONSE_REGULAR) {
            return $this->templates[$this->actionName];
        }
        if ($this->responseType == self::RESPONSE_REDIRECT) {
            return self::ROUTE_INDEX;
        }
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        $result = $this->submitForm();
        if ($result !== true) {
            return array(
                'form' => $result,
            );
        }
        else {
            return array(
                'menu' => $this->shortClassName,
            );
        }
    }

    /**
     * Submits the form and flushes the Entity
     *
     * @return bool
     */
    private function submitForm() {
        $form = $this->formFactory->create($this->form, $this->formObj);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $newParameters = $form->getData();
            $this->dumpModifiedParamsAsYaml($newParameters);
            $this->logger->debug('clearing cache for new settings');
            try {
                $this->environmentManager->clearCache();
            } catch (\Exception $e) {
                $this->logger->error('failed to clear cache', array($e));
            }

            $this->responseType = self::RESPONSE_REDIRECT;
            return true;
        }
        $this->responseType = self::RESPONSE_REGULAR;
        return $form->createView();
    }

    /**
     * Dumps the modified parameters into parameter.yml
     *
     * @param $newParameters
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function dumpModifiedParamsAsYaml($newParameters)
    {
        if (!array_key_exists('parameters', $newParameters)) {
            $newParameters = array('parameters' => $newParameters);
        }

        $dumper = new Dumper();
        $newParametersAsYaml = $dumper->dump($newParameters, self::DUMP_LEVEL);

        $parameterPath = $this->getParametersFilePath();
        file_put_contents($parameterPath, $newParametersAsYaml);
    }

    /**
     * @return string
     */
    private function getParametersFilePath()
    {
        return sprintf('%s/config/parameters.yml', $this->rootDir);
    }
}
