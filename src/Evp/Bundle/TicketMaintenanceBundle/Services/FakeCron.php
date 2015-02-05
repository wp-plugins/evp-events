<?php
/**
 * FakeCron class in case when system cron is not available
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketMaintenanceBundle\Services;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * Class FakeCron
 */
class FakeCron {

    const DUMP_LEVEL = 2;
    const PARAMS_YML = '/config/parameters.yml';

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
     */
    private $command;

    /**
     * Sets RootDir
     *
     * @param string $rootDir
     */
    public function setRootDir($rootDir) {
        $this->rootDir = $rootDir;
    }

    /**
     * Sets the Command to execute
     *
     * @param \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand $cmd
     */
    public function setCommand(ContainerAwareCommand $cmd) {
        $this->command = $cmd;
    }

    /**
     * Enables FakeCron by setting fake_cron param to true
     */
    public function enable() {
        $params = $this->loadParametersFromYaml();
        $params['fake_cron'] = true;
        $this->dumpModifiedParamsAsYaml($params);
    }

    /**
     * Disables FakeCron by setting fake_cron param to true
     */
    public function disable() {
        $params = $this->loadParametersFromYaml();
        $params['fake_cron'] = false;
        $this->dumpModifiedParamsAsYaml($params);
    }

    /**
     * Executes the MarkExpiredOrders Command
     */
    public function markExpiredOrders() {
        $input = new ArgvInput(array('argv' => ''));
        $output = new ConsoleOutput;

        $this->command->run($input, $output);
    }

    /**
     * Load the parameters from the parameters.yml file
     * @author Dmitrijus Glezeris <d.glezeris@evp.lt>
     *
     * @return array
     */
    private function loadParametersFromYaml() {
        $content = file_get_contents($this->rootDir .self::PARAMS_YML);

        $yaml = new Parser;
        $parsedContent = $yaml->parse($content, false, true);
        $parameters = $parsedContent['parameters'];
        return $parameters;
    }

    /**
     * Dumps the modified parameters into parameter.yml
     * @author Dmitrijus Glezeris <d.glezeris@evp.lt>
     *
     * @param $newParameters
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function dumpModifiedParamsAsYaml($newParameters)
    {
        if (!array_key_exists('parameters', $newParameters)) {
            $newParameters = array('parameters' => $newParameters);
        }

        $dumper = new Dumper;
        $newParametersAsYaml = $dumper->dump($newParameters, self::DUMP_LEVEL);
        file_put_contents($this->rootDir .self::PARAMS_YML, $newParametersAsYaml);
    }
} 
